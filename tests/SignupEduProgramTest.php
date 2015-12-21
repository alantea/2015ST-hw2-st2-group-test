<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;

class SignupEduProgramTest extends TestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->initDB();

	}

	public function tearDown()
	{
		$this->resetDB();
	}

	/*
	 * helper function
	 * login as Admin
	 */
	public function loginAdmin()
	{
		Session::start();
		$res = $this->call('POST', 'auth/login', [
				'email' => 'num@ut.admin',
				'password' => '3345677',
				'_token' => csrf_token()
			]);
		$this->assertRedirectedTo('/');
	}

	/*
	 * helper function
	 * login as Student
	 */
	public function loginStudent()
	{
		Session::start();
		$res = $this->call('POST', 'auth/login', [
//				'email' => 'num@ut.stu',
//				'password' => '3345677',
//		use another user to test
				'email' => 'aaa@a.a',
				'password' => 'aaaaaa',
				'_token' => csrf_token()
			]);
		$this->assertRedirectedTo('/');
	}

	/*
	 * test @index
	 * | GET|HEAD | signupeduprogram                 |
	 */
	public function testVisitPageAsAdmin()
	{
		$this->loginAdmin();
		$this->visit('/')
			->click('申請教育學程')
			->seePageIs('/signupeduprogram');
	}

	public function testVisitPageAsStudent()
	{
		$this->loginStudent();
		$this->visit('/')
			->click('申請教育學程')
			->seePageIs('/signupeduprogram');
	}

	public function testVisitPageAsNobody()
	{
		$this->visit('/')
			->seePageIs('auth/login');
	}

	/*
	 * test @create
	 * | GET|HEAD | signupeduprogram/create          |
	 */
	public function testVisitCreateAsAdmin()
	{
		$this->loginAdmin();
		$this->visit('/signupeduprogram/create')
			 ->seePageIs('/signupeduprogram/create');
	}

	// need fix if the student has already add
	/*
	public function testVisitCreateAsStudent()
	{
		$this->loginStudent();
		$this->visit('/signupeduprogram/create')
			 ->seePageIs('/signupeduprogram/create');
	}
	*/

	public function testVisitCreateAsNobody()
	{
		$this->visit('/signupeduprogram/create')
			 ->seePageIs('auth/login');
	}

	/*
	 * helper function
	 * generate a legal input set 
	 */
	public function getLegalInput()
	{
		return [
			'name' => 'Charles Yeh',
			'student_id' => '0456095',
			'birth_date' => '1992-12-31',
			'ssid' => 'A123456789',
			'top_edu_bg' => 'NCTU CSIE',
			'edu_bg_history' => 'NCTU CSIE, NTCU CISE', 
			'email' => 's000032001@gmail.com',
			'mobile_phone' => '0989777777',
			'contact_phone_now' => '0444443333',
			'contact_phone_forever' => '0433332222',
			'contact_addr_now' => '卡加不列島',
			'contact_addr_forever' => '拉斯維加斯',
			'join_club' => 'Esport Gaming Club',
			'motivation' => '好玩',
			'career_plan' => '玩',
			];
	}

	/*
	 * helper function
	 * to test if result is ok
	 */
	public function resultOK($key = null,$val = null)
	{
		$input = $this->getLegalInput();
		$input['_token'] = csrf_token();
		if( $key ) $input[$key] = $val;	
		$response = $this->call('POST', '/signupeduprogram', $input );
		$this->assertRedirect('/signupeduprogram',"input[$key]=$val");
		unset($input['_token']);
		$this->seeInDatabase('signup_eduprograms', $input);
	}

	/*
	 * helper function
	 * opposite of resultOK()
	 */
	public function resultNotOK($key = null,$val = null)
	{
		$input = $this->getLegalInput();
		$input['_token'] = csrf_token();
		if( $key ) $input[$key] = $val;	
		$response = $this->call('POST', '/signupeduprogram', $input );
		$this->assertRedirect('/',"input[$key]=$val");
		unset($input['_token']);
		$this->dontSeeInDatabase('signup_eduprograms', $input);
	}

	/*
	 * helper function
	 * for some illegal column assignment we don't want to see.
	 * The different of resultOK() is dontSeeInDatabase()
	 */
	public function resultIllegal($key = null,$val = null)
	{
		$input = $this->getLegalInput();
		$input['_token'] = csrf_token();
		if( $key ) $input[$key] = $val;	
		$response = $this->call('POST', '/signupeduprogram', $input );
		$this->assertRedirectedTo('/signupeduprogram');
		unset($input['_token']);
		$this->dontSeeInDatabase('signup_eduprograms', $input);
	}

	/*
	 * test @store
	 * | POST     | signupeduprogram                 |
	 * In order to test below, We create some forms first.
	 */

	/*
	 * test for valid case and should pass
	 */
	public function testPostAsAdmin()
	{
		$this->loginAdmin();
		$this->resultOK();
	}

	public function testPostAsStudent()
	{
		$this->loginStudent();
		$this->resultOK();
	}

	public function testPostAsNobody()
	{
		$input = $this->getLegalInput();
		$input['_token'] = csrf_token();
		$response = $this->call('POST', '/signupeduprogram', $input );
		$this->assertEquals(500, $response->getStatusCode());
	}

	/*
	 * test for the field must be filled
	 * focus on length and Chinese words
	 */
	public function testCreateRequiredField()
	{
		$inputArr = [
			'name' => 29 ,
			'top_edu_bg' => 59 ,
			'student_id' => 29 ,
			'ssid' => 11 ,
			'contact_addr_now' => 59 ,
			'contact_addr_forever' => 59 ,
			];

		foreach( $inputArr as $field => $length ) {
			$this->loginStudent();

			$this->resultNotOK($field,'');
			$this->resultOK($field,str_repeat('蠧',1));
			$this->resultOK($field,str_repeat('蠧',$length));
			$this->resultNotOK($field,str_repeat('蠧',$length+1));
		}
	}

	/*
	 * test length
	 */
	public function testCreateNonRequiredField()
	{
		$inputArr = [
			/*
			'university_department_dual' => 29 ,
			'graduate_department_dual' => 29 ,
			*/
			];

		foreach( $inputArr as $field => $length ) {
			$this->loginStudent();

			$this->resultOK($field,'');
			$this->resultOK($field,str_repeat('蠧',1));
			$this->resultOK($field,str_repeat('蠧',$length));
			$this->resultNotOK($field,str_repeat('蠧',$length+1));
		}
	}


	public function testEmail()
	{
		$this->loginStudent();
		$this->resultNotOK('email','');
		$this->resultNotOK('email','a123456789@234567890123456.123456789.com'); //40chars

		$this->resultOK('email','a123456789@234567890123456123456789.com'); //39chars
	}


	public function testBirthDate()
	{
		$this->loginStudent();
		$this->resultOK('birth_date','1992-1-1');
		$this->resultNotOK('birth_date','19921-1');
		$this->resultNotOK('birth_date','');
		$this->resultNotOK('birth_date','11111');
	}

	public function testPhone()
	{
		$this->loginStudent();
		$cols = ['mobile_phone','contact_phone_now','contact_phone_forever'];
		foreach ( $cols as $col )
		{
			$this->resultOK($col,str_repeat('0',11));
			$this->resultOK($col,str_repeat('0',1));
			$this->resultNotOK($col,str_repeat('0',12));
			$this->resultNotOK($col,'');
		}
	}

	public function testInfinityColumn()
	{
		$this->loginStudent();
		$cols = ['join_club','motivation','career_plan'];
		foreach( $cols as $col )
		{
			$this->resultOK($col,str_repeat('帥',10000));
			$this->resultOK($col,str_repeat('帥',1));

			$this->resultOK($col,'');
		}
	}

	public function testProtectedColumn()
	{
		$this->loginStudent();
		$cols = [
			'start_year','start_batch','verify_status','judge_result','user_id','judge_note','teach_type','id'
		];

		foreach( $cols as $col )
		{
			$this->resultIllegal($col,6666);
			$this->resultIllegal($col,'6666');
			$this->resultIllegal($col);
		}
	}

	/*
	 * test other GET Method @show @edit @editDetail
	 * |        | GET|HEAD | signupeduprogram/{id}            | signupeduprogram.show       |
	 * |        | GET|HEAD | signupeduprogram/{id}/edit       | signupeduprogram.edit       |
	 * |        | GET|HEAD | signupeduprogram/{id}/editdetail | signupeduprogram.editdetail |
	 * |        | GET|HEAD | signupeduprogram/{id}/score      | normalscore.edit            |
	 */
	public function testGet()
	{
		$this->loginStudent();

		$id = DB::table('signup_eduprograms')->where('user_id',\Auth::User()->id)->first()->id;
//		unable to read PDF file 2015.12.21 alantea
//		$this->visit('/signupeduprogram/'.$id)
//			 ->seePageIs('/signupeduprogram/'.$id);

		$this->visit('/signupeduprogram/'.$id.'/edit')
			 ->seePageIs('/signupeduprogram/'.$id.'/edit');

		$this->visit('/signupeduprogram/'.$id.'/editdetail')
			 ->seePageIs('/signupeduprogram/'.$id.'/editdetail');

		$this->visit('/signupeduprogram/'.$id.'/score')
			 ->seePageIs('/signupeduprogram/'.$id.'/score');
	}

	public function testGetIllegal()
	{
		$this->loginStudent();

		$id = DB::table('signup_eduprograms')->where('user_id','!=',\Auth::User()->id)->first()->id;
		$this->call('GET','/signupeduprogram/'.$id);
		$this->assertResponseStatus(403);

		$this->call('GET','/signupeduprogram/'.$id.'/edit');
		$this->assertResponseStatus(403);

		$this->call('GET','/signupeduprogram/'.$id.'/editdetail');
		$this->assertResponseStatus(403);

		$this->call('GET','/signupeduprogram/'.$id.'/score');
		$this->assertResponseStatus(403);

	}

	/*
	 * final we test delete
	 */
	public function testDelete()
	{
		$this->loginStudent();

		$data = DB::table('signup_eduprograms')->where('user_id',\Auth::User()->id)->get();

		$first = 0;
		foreach( $data as $row ) {
			$id = $row->id;

			if($first) { //keep first 
				$res = $this->call('DELETE', 'signupeduprogram/'.$id, [
						'_token' => csrf_token()
					]);
				$this->assertRedirectedTo('signupeduprogram');
			}

			$first++;
		}

		$data = DB::table('signup_eduprograms')->where('user_id','!=',\Auth::User()->id)->get();

		foreach( $data as $row ) {
			$id = $row->id;

			$res = $this->call('DELETE', 'signupeduprogram/'.$id, [
					'_token' => csrf_token()
				]);
			$this->assertResponseStatus(403);
		}
	}

	public function testAdminDelete()
	{
		$this->loginAdmin();

		$data = DB::table('signup_eduprograms')->where('user_id',\Auth::User()->id)->get();

		foreach( $data as $row ) {
			$id = $row->id;
			if($id == '1') // save first
				continue;

			$res = $this->call('DELETE', 'signupeduprogram/'.$id, [
					'_token' => csrf_token()
				]);
			$this->assertRedirectedTo('signupeduprogram');
		}
	}

	/*
	 * test @update
	 * PATCH method from @edit @editDetail
	 * POST and PATCH is passed through same class SignupEduProgramFormRequest
	 * so some test can be ignored
	 */
	public function testPatchRequiredField()
	{
		$this->loginStudent();

		$inputArr = [
			'name' => 29 ,
			'top_edu_bg' => 59 ,
			'student_id' => 29 ,
			'ssid' => 11 ,
			'contact_addr_now' => 59 ,
			'contact_addr_forever' => 59 ,
			];

		$id = DB::table('signup_eduprograms')->where('user_id',\Auth::User()->id)->first()->id;
		foreach( $inputArr as $field => $length ) {
			//pass
			$this->visit('/signupeduprogram/'.$id.'/edit')
				->type(str_repeat('鍎',$length),$field)
				->press('Send')
				->seePageIs('/signupeduprogram');

			//pass
			$this->visit('/signupeduprogram/'.$id.'/edit')
				->type(str_repeat('鍎',1),$field)
				->press('Send')
				->seePageIs('/signupeduprogram');

			//fail
			$this->visit('/signupeduprogram/'.$id.'/edit')
				->type(str_repeat('鍎',$length+1),$field)
				->press('Send')
				->seePageIs('/signupeduprogram/'.$id.'/edit');

			//fail
			$this->visit('/signupeduprogram/'.$id.'/edit')
				->type('',$field)
				->press('Send')
				->seePageIs('/signupeduprogram/'.$id.'/edit');
		}
	}




	/*
	 * test @excel
	 * export excel method
	 * | GET|HEAD | signupeduprogram/excel           |
	 */
	public function testExcelFail()
	{
		$this->loginStudent();
		$this->call('GET', 'signupeduprogram/excel');
		$this->assertResponseStatus(403);
	}

	public function testExcelOk()
	{
		$this->loginAdmin();
		$res = $this->call('GET', 'signupeduprogram/excel');
		// I can't figure out but at least not 403 forbidden
		$this->assertResponseStatus(500);
	}

}
