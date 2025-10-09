<?php

namespace App\Http\Controllers;

use App\Models\StaffUser;
use App\Models\GeneralCatalogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Rules\ReCaptcha;

class StaffUserController extends Controller
{
    public function index2()
    {
        $staffUsers = StaffUser::all();
        return view('staff_users.index', compact('staffUsers'));
    }

    public function showLoginForm()
    {
        return view('login');
    }


    public function login(Request $request){

        $request->validate([
            'email'             => 'required',
            'password'          => 'required',
            // 'g-recaptcha-response' => ['required', new ReCaptcha]
        ], [
            'email.required'    => 'Usuario requerido',
          //  'email.email'       => 'Debes agregar un correo válido',
            'password.required' => 'Contraseña requerida',
            // 'g-recaptcha-response.required' => 'Validación requerida'
        ]);
        $credentials = $request->only('email', 'password');
     
        if (Auth::attempt($credentials)) {
            // Obtener el usuario autenticado
            $user = Auth::user();
            /*
            // Verificar si la cuenta está activa
            if ($user->activate != 1) {
                Auth::logout(); // Desconectar al usuario
                return back()->withErrors(['general' => 'La cuenta no está activada']);
            }*/
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'general' => 'Correo electrónico o contraseña son incorrectas',

        ]);
    }

    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
        // Redirige a la página de inicio o donde desees
    }


    public function index(Request $request){

        $catalogs   = new GeneralCatalogs();
        $activePage = 'users';
        $staff_users = StaffUser::all();

        return view('usuarios.staff_users', compact('activePage', 'catalogs', 'staff_users'));

    }

    public function user_form(Request $request){

        $catalogs   = new GeneralCatalogs();
        $activePage = 'users';
    

        if ($request->usuario_id) {
            $user = StaffUser::where('id', $request->usuario_id )->first();
        }else{
            $user = null;
        }

        return view('usuarios.staff_users_form', compact('activePage', 'catalogs', 'user'));

    }

    public function user_submit_form(Request $request){
    

        if ($request->id) {
            $StaffUser = StaffUser::find( $request->id );
            
            if ($request->password) {
                $request->validate([
                    'name'      => 'required',
                    'email'     => ['required', 'unique:staff_users,email,' . $request->id],
                    'role'      => 'required',
                    'active'    => 'required',
                    'password'  => 'required|min:6|confirmed',
                    'password_confirmation' => 'required'
                ], [
                    'name.required'      => 'Campo requerido',
                    'email.required'     => 'Usuario requerido',
                   // 'email.email'        => 'Debes agregar un correo válido',
                    'email.unique'       => 'El usuario ya existe, favor de ingresar otro',
                    'role.required'      => 'Campo ROL requerido',
                    'active.required'    => 'Campo STATUS requerido',
                    'password.required'  => 'Contraseña requerida',
                    'password.min'       => 'Contraseña debe tener por lo menos 6 carácteres',
                    'password.confirmed' => 'Las contraseñas no coinciden.'
                ]);

                $StaffUser->name   = $request->name;
                $StaffUser->email  = $request->email;
                $StaffUser->role   = $request->role;
                $StaffUser->active = $request->active;
                $StaffUser->password = Hash::make($request->password);

            }else{
                $request->validate([
                    'name'      => 'required',
                    'email'     => ['required', 'email', 'unique:staff_users,email,' . $request->id],

                  //  'email' => ['required', 'email', 'unique:users,email,' . auth()->id()],

                    'role'      => 'required',
                    'active'    => 'required'
                ], [
                    'name.required'      => 'Campo requerido',
                    'email.required'     => 'Usuario requerido',
                    //'email.email'        => 'Debes agregar un correo válido',
                    'email.unique'       => 'El usuario ya existe, favor de ingresar otro',
                    'role.required'      => 'Campo ROL requerido',
                    'active.required'    => 'Campo STATUS requerido'
                ]);

            }

            $StaffUser = StaffUser::find( $request->id );
            $StaffUser->name   = $request->name;
            $StaffUser->email  = $request->email;
            $StaffUser->role   = $request->role;
            $StaffUser->active = $request->active;

        }else{
            $request->validate([
                'name'      => 'required',
                'email'     => 'required|unique:staff_users',
                'role'      => 'required',
                'active'    => 'required',
                'password'  => 'required|min:6|confirmed',
                'password_confirmation' => 'required'
            ], [
                'name.required'      => 'Campo requerido',
                'email.required'     => 'Usuario requerido',
                //'email.email'        => 'Debes agregar un correo válido',
                'email.unique'       => 'El usuario ya existe, favor de ingresar otro',
                'role.required'      => 'Campo ROL requerido',
                'active.required'    => 'Campo STATUS requerido',
                'password.required'  => 'Contraseña requerida',
                'password.min'       => 'Contraseña debe tener por lo menos 6 carácteres',
                'password.confirmed' => 'Las contraseñas no coinciden.'
            ]);

            $user_email = StaffUser::where('email', $request->email )->first();
     
            if($user_email){
                return redirect('crear_usuario')->with('error', 'Corro electrónico registrado. Debes de usar otro diferente')->withInput();
            }

            $StaffUser           = new StaffUser();
            $StaffUser->name     = $request->name;
            $StaffUser->email    = $request->email;
            $StaffUser->role     = $request->role;
            $StaffUser->active   = $request->active;
            $StaffUser->password = Hash::make($request->password);


        }

        if(!$StaffUser->save()){
            return redirect('usuarios')->with('error', 'Error')->withInput();
        }else{
            return redirect('usuarios')->with('success', 'Usario Agregado')->withInput();
        }

    }


    public function delete_user( $user_id){ 
        $catalogs        = new GeneralCatalogs();
        $activePage      = 'usuarios';
        $user         = StaffUser::where('id', $user_id)->first();
        $user->active = null;

        $user->save();

        $users       = StaffUser::where('active', 1)
                            ->orderBy('id', 'desc')
                            ->get();

        return view('usuarios', compact('activePage', 'catalogs', 'users'));
    }


}


