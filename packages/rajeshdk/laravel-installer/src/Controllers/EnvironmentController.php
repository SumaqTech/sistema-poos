<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use App\Models\Access\User\User;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use RachidLaasri\LaravelInstaller\Events\EnvironmentSaved;
use RachidLaasri\LaravelInstaller\Helpers\EnvironmentManager;
use Validator;
use Illuminate\Support\Facades\URL;

class EnvironmentController extends Controller
{
    /**
     * @var EnvironmentManager
     */
    protected $EnvironmentManager;

    /**
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(EnvironmentManager $environmentManager)
    {
        $this->EnvironmentManager = $environmentManager;
    }

    /**
     * Display the Environment menu page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentMenu()
    {
        return view('vendor.installer.environment');
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentWizard()
    {

        $envConfig = $this->EnvironmentManager->getEnvContent();

        if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') or !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            URL::forceScheme('https');
        }

        return view('vendor.installer.environment-wizard', compact('envConfig'));
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentClassic()
    {

        return null;
    }

    /**
     * Processes the newly saved environment configuration (Classic).
     *
     * @param Request $input
     * @param Redirector $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveClassic(Request $input, Redirector $redirect)
    {
        return false;
    }

    /**
     * Processes the newly saved environment configuration (Form Wizard).
     *
     * @param Request $request
     * @param Redirector $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveWizard(Request $request, Redirector $redirect)
    {
        try {

            $flag = true;
            $code = '';
            $status = 'Success';
            $rules = config('installer.environment.form.rules');
            $messages = [
                'environment_custom.required_if' => trans('installer_messages.environment.wizard.form.name_required'),
            ];
            $m = '<ul>';

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                $status = 'Error';
                $flag = false;
                foreach ($validator->errors()->all() as $error) {
                    $m .= '<li>' . $error . '</li>';
                }
                $code = 'F3';
            }

            if (!$this->checkDatabaseConnection($request)) {
                $status = 'Error';
                $flag = false;
                $m .= '<li>' . trans('installer_messages.environment.wizard.form.db_connection_failed') . '</li>';
                $code = 'D6';
            }

            $this->confirmValid($request);

            $m .= '</ul>';


            if ($flag) {
                $m .= '<li>Installation Success, Installer locked to prevent re-installation</li>';
                $results = $this->EnvironmentManager->saveFileWizard($request);
                event(new EnvironmentSaved($request));
                return json_encode(array('status' => $status, 'message' => $m));
            } else {
                return json_encode(array('status' => $status, 'message' => $m));
            }
        } catch (Exception $e){
            return json_encode(array('status' => 'Error', 'message' => 'Indicating Unsupported PHP Version & '.$e->getMessage()));
        }

    }

    /**
     * TODO: We can remove this code if PR will be merged: https://github.com/RachidLaasri/LaravelInstaller/pull/162
     * Validate database connection with user credentials (Form Wizard).
     *
     * @param Request $request
     * @return bool
     */
    private function checkDatabaseConnection(Request $request)
    {
        $connection = $request->input('database_connection');
        DB::purge('mysql');
        Config::set('database.connections.mysql', [
            'driver' => $connection,
            'host' => $request->input('database_hostname'),
            'port' => $request->input('database_port'),
            'database' => $request->input('database_name'),
            'username' => $request->input('database_username'),
            'password' => $request->input('database_password'),
        ]);
        try {
            DB::connection('mysql')->getPdo();
            if (DB::connection('mysql')->getDatabaseName()) {
                return true;

            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    private function confirmValid(Request $request)
    {

        $contents = file_get_contents(storage_path('rose-crm.sql'));
        if ($contents !== false) {
            $connection = $request->input('database_connection');
            DB::purge('mysql');
            Config::set('database.connections.mysql', [
                'driver' => $connection,
                'host' => $request->input('database_hostname'),
                'port' => $request->input('database_port'),
                'database' => $request->input('database_name'),
                'username' => $request->input('database_username'),
                'password' => $request->input('database_password'),
            ]);
            try {
                $conn = DB::connection('mysql');
                if ($conn->getDatabaseName()) {
                    ini_set('memory_limit', '-1');
                    $conn->unprepared($contents);
                    $conn->commit();

                    $p_file = public_path('conf.json');
                    if(!file_exists($p_file)) {
                        $nf= fopen($p_file,'wb');
                        fclose($nf);
                        chmod($p_file, 0755);
                    }
                    if (is_writeable($p_file)) {
                        $dir = URL::to('/');
                        $ciph = "AES-128-CTR";
                        $length = openssl_cipher_iv_length($ciph);
                        $options = 0;
                        $enc = '1234567891012021';
                        $enc_k = config('release.string');
                        $code = openssl_encrypt($dir, $ciph, $enc_k, $options, $enc);
                        file_put_contents($p_file, $code);
                        $lc = file_get_contents($p_file);
                        if (empty($lc)) {
                            return array('flash_error' => 'Server write permissions denied');
                        } else {
                            return array('flash_success' => 'License updated!');
                        }
                    } else {
                        chmod($p_file, 0755);
                        return array('flash_error' => 'Server write permissions denied!');
                    }
                    Artisan::call("optimize");
                    return true;

                } else {
                    return array('status' => 'Error', 'message' => trans('installer_messages.environment.wizard.form.db_connection_failed'));

                }
            } catch (\Exception $e) {
                return array('status' => 'Error', 'message' => trans('installer_messages.environment.wizard.form.db_connection_failed'));

            }
        } else {
            return false;
        }

    }

}
