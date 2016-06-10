<?php

use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;


class LoginTest extends TestCase
{
    use DatabaseTransactions;

    /*
     * Testando se ao acessar a rota home(Dashboard) sem estar logado, ela redirecionara(302) para a página de login e certificando-se que não ha autenficação
     */
    /** @test */
    public function homeWithoutLoggedTest(){
        $this->get('home');
        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/login');
        $this->assertEquals(false, Auth::check());
    }

    /*
     * Testando o acesso a rota home(Dashboard), estando logado no sistema.
     */
    /** @test */
    public function homeWithLoggedTest(){

        $User = User::create([
            'email'     => 'testing@testing.com',
            'password'  => Hash::make('testing')
        ]);

        $this->be($User);
        $this->get('home');
        $this->assertResponseStatus(200);
        $this->assertEquals(true, Auth::check());
    }

    /*
     * Testando o usuário logado consegue fazer logout, ser redirecionado(302) para a Landing Page(comportamento padrão) e não estar mais autenticado
     */
    /** @test */
    public function logoutUserTest()
    {
        $User = User::create([
            'email'    => 'testing@testing.com',
            'password' => Hash::make('testing')
        ]);

        $this->be($User);
        $this->get('logout');
        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/');
        $this->assertEquals(false, Auth::check());
    }


    /*
     * Testando se um usuário que existe no banco de dados, consegue fazer login no sistema normalmente
     */
    /** @test */
    public function loginUserCorrectTest()
    {
        $User = User::create([
            'email'     => 'testing@testing.com',
            'password'  => Hash::make('testing'),
        ]);

        $credentials = [
            'email'     => 'testing@testing.com',
            'password'  => 'testing',
        ];

        $this->post('login', $credentials);
        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/');
        $this->assertEquals(true, Auth::check());
    }

    /*
     * Testando se um usuário que não lembra sua senha e a informou erroneamente, consegue fazer login normalmente
     */
    /** @test */
    public function loginUserWrongTest()
    {
        $User = User::create([
            'email'     => 'testing@testing.com',
            'password'  => Hash::make('testing'),
        ]);

        $credentials = [
            'email'     => 'testing@testing.com',
            'password'  => 'test',//Observe que este password não corresponde ao cadastrado no sistema
        ];

        $this->post('login', $credentials);
        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/');
        $this->assertEquals(false, Auth::check());
    }

    /*
     * Testando se um possível usuário consegue acessar a página e se cadastrar no sistema
     */
    /** @test */
    public function registerUserTest(){

        $formData = [
            'name'                  => 'usertest',
            'email'                 => 'testing@testing.com',
            'password'              => 'testing',
            'password_confirmation' => 'testing'
        ];

        $this->post('register', $formData);
        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/');
        $this->seeInDatabase('users', ['name' => 'usertest', 'email' => 'testing@testing.com']);
        $this->assertEquals(true, Auth::check());
    }
}
