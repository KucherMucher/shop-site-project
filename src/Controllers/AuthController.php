<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Company;
use App\Models\Notification;
use App\Models\User;
use App\Support\MailService;

final class AuthController extends Controller
{
    public function showLogin(): string
    {
        return $this->render('auth/login', ['title' => t('nav.login') . ' · ' . config('app.name')], 'layouts/auth');
    }

    public function login(): never
    {
        $email = strtolower(trim((string) request_input('email', '')));
        $password = (string) request_input('password', '');

        $validator = new Validator(['email' => $email, 'password' => $password]);
        $validator->required('email', t('auth.email_required'))->email('email', t('auth.email_required'))
            ->required('password', t('auth.invalid_credentials'));

        if ($validator->fails()) {
            Session::errors($validator->errors());
            Session::old(['email' => $email]);
            redirect('/login');
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            Session::errors(['email' => t('auth.invalid_credentials')]);
            Session::old(['email' => $email]);
            redirect('/login');
        }

        if ($user['status'] === 'suspended') {
            Session::errors(['email' => t('auth.account_suspended')]);
            Session::old(['email' => $email]);
            redirect('/login');
        }

        if ($user['role_slug'] === 'company' && $user['status'] !== 'active') {
            $company = Company::findForUser((int) $user['id']);
            Session::old(['email' => $email]);
            Session::errors(['email' => $company && $company['status'] === 'rejected' ? t('auth.company_rejected_text') : t('auth.company_pending_text')]);
            redirect('/login');
        }

        if ($user['status'] !== 'active') {
            $token = bin2hex(random_bytes(32));
            User::update((int) $user['id'], [
                'verification_token' => $token,
                'verification_expires_at' => date('Y-m-d H:i:s', time() + 86400),
            ]);
            MailService::sendVerification($user, $token);
            Session::set('verify_email', $user['email']);
            Session::errors(['email' => t('auth.account_pending')]);
            redirect('/verificar-email');
        }

        User::update((int) $user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
        $user = User::find((int) $user['id']);
        Auth::login($user);

        $intended = Session::get('intended_url');
        Session::forget('intended_url');
        Session::flash('success', t('auth.login_success'));

        if ($intended) {
            redirect($intended);
        }
        if ($user['role_slug'] === 'admin' || $user['role_slug'] === 'moderator') {
            redirect('/admin');
        }
        if ($user['role_slug'] === 'company') {
            redirect('/empresa/painel');
        }
        redirect('/conta');
    }

    public function showRegister(): string
    {
        $type = ($_GET['tipo'] ?? 'user') === 'company' ? 'company' : 'user';
        return $this->render('auth/register', [
            'title' => t('auth.register_title') . ' · ' . config('app.name'),
            'type' => $type,
        ], 'layouts/auth');
    }

    public function register(): never
    {
        $type = (string) request_input('account_type', 'user');
        $name = trim((string) request_input('name', ''));
        $email = strtolower(trim((string) request_input('email', '')));
        $password = (string) request_input('password', '');
        $companyName = trim((string) request_input('company_name', ''));
        $terms = isset($_POST['terms']);

        $validator = new Validator($_POST);
        $validator->required('name', t('company.required_fields'))
            ->required('email', t('auth.email_required'))->email('email', t('auth.email_required'))
            ->required('password', t('auth.password_hint'))->min('password', 8, t('auth.password_hint'))
            ->confirmed('password', t('auth.password_mismatch'));

        if ($type === 'company') {
            $validator->required('company_name', t('company.required_fields'));
        }
        if (!$terms) {
            $validator->add('terms', t('auth.terms_required'));
        }
        if (User::emailExists($email)) {
            $validator->add('email', t('auth.email_taken'));
        }
        if ($validator->fails()) {
            Session::errors($validator->errors());
            Session::old($_POST);
            redirect('/registo' . ($type === 'company' ? '?tipo=company' : ''));
        }

        if ($type === 'company') {
            $slugBase = slugify($companyName) ?: 'empresa';
            $slug = $slugBase;
            $i = 1;
            while (Database::scalar('SELECT COUNT(*) FROM companies WHERE slug = ?', [$slug]) > 0) {
                $slug = $slugBase . '-' . (++$i);
            }

            Database::transaction(function () use ($name, $email, $password, $companyName, $slug) {
                $companyId = Company::create([
                    'name' => $companyName,
                    'slug' => $slug,
                    'vat' => trim((string) request_input('vat', '')),
                    'contact_name' => $name,
                    'email' => $email,
                    'phone' => trim((string) request_input('phone', '')),
                    'website' => trim((string) request_input('website', '')),
                    'address' => trim((string) request_input('address', '')),
                    'city' => trim((string) request_input('city', '')),
                    'postal_code' => trim((string) request_input('postal_code', '')),
                    'status' => 'pending',
                ]);
                $roleId = (int) Database::scalar("SELECT id FROM roles WHERE slug = 'company'");
                $userId = User::create([
                    'role_id' => $roleId,
                    'company_id' => $companyId,
                    'name' => $name,
                    'email' => $email,
                    'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                    'phone' => trim((string) request_input('phone', '')),
                    'status' => 'pending',
                    'email_verified_at' => date('Y-m-d H:i:s'),
                ]);
                Notification::notifyAdmins(
                    'company',
                    t('notify.new_company_title'),
                    t('notify.new_company_message', ['company' => $companyName]),
                    '/admin/empresas'
                );
                MailService::sendWelcomeCompany(['id' => $companyId, 'name' => $companyName], ['id' => $userId, 'name' => $name, 'email' => $email]);
            });

            Session::flash('success', t('auth.register_company_success'));
            redirect('/empresa/pendente');
        }

        $token = bin2hex(random_bytes(32));
        $userId = User::create([
            'role_id' => (int) Database::scalar("SELECT id FROM roles WHERE slug = 'user'"),
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'phone' => trim((string) request_input('phone', '')),
            'status' => 'pending',
            'verification_token' => $token,
            'verification_expires_at' => date('Y-m-d H:i:s', time() + 86400),
            'locale' => locale(),
        ]);

        $user = User::find($userId);
        MailService::sendVerification($user, $token);
        Session::set('verify_email', $email);
        Session::set('dev_verify_link', config('app.debug') ? email_url('/verificar-email?token=' . urlencode($token)) : null);

        Session::flash('success', t('auth.register_success'));
        redirect('/verificar-email');
    }

    public function verifyEmail(): string
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        $user = $token !== '' ? User::verifyToken($token) : null;

        if (!$user) {
            Session::flash('error', t('auth.invalid_token'));
            return $this->render('auth/verify', [
                'title' => t('auth.verify_title'),
                'email' => Session::get('verify_email'),
                'invalid' => true,
            ], 'layouts/auth');
        }

        User::update((int) $user['id'], [
            'status' => 'active',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'verification_token' => null,
            'verification_expires_at' => null,
        ]);
        Session::flash('success', t('auth.verified'));
        redirect('/login');
    }

    public function showVerify(): string
    {
        if (trim((string) ($_GET['token'] ?? '')) !== '') {
            return $this->verifyEmail();
        }
        return $this->render('auth/verify', [
            'title' => t('auth.verify_title') . ' · ' . config('app.name'),
            'email' => Session::get('verify_email'),
            'invalid' => false,
            'devLink' => Session::get('dev_verify_link'),
        ], 'layouts/auth');
    }

    public function resendVerification(): never
    {
        $email = strtolower(trim((string) request_input('email', '')));
        $user = User::findByEmail($email);
        if ($user && $user['status'] === 'pending' && empty($user['email_verified_at'])) {
            $token = bin2hex(random_bytes(32));
            User::update((int) $user['id'], [
                'verification_token' => $token,
                'verification_expires_at' => date('Y-m-d H:i:s', time() + 86400),
            ]);
            MailService::sendVerification($user, $token);
            Session::set('verify_email', $email);
            if (config('app.debug')) {
                Session::set('dev_verify_link', email_url('/verificar-email?token=' . urlencode($token)));
            }
        }
        Session::flash('success', t('auth.resent'));
        redirect('/verificar-email');
    }

    public function logout(): never
    {
        Auth::logout();
        Session::flash('success', t('auth.logout_success'));
        redirect('/');
    }
}
