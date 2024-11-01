<?php

namespace VOCheck;

class BrowserStack
{
    private $username;
    private $accessKey;

    public function __construct($username, $accessKey)
    {
        $this->username = $username;
        $this->accessKey = $accessKey;
        $this->apiUrl = 'https://www.browserstack.com/screenshots';
    }

    public function getBrowsers()
    {
        return $this->sendRequest('GET', '/browsers.json');
    }

    public function generateScreenshots($url)
    {
        $browsers = [
            [
                "os" => "Windows",
                "os_version" => "XP",
                "browser" => "chrome",
                "device" => null,
                "browser_version" => "43.0",
            ],
            [
                "os" => "Windows",
                "os_version" => "10",
                "browser" => "chrome",
                "device" => null,
                "browser_version" => "50.0",
            ],
            [
                "os" => "ios",
                "os_version" => "8.3",
                "browser" => "Mobile Safari",
                "device" => "iPhone 6",
                "browser_version" => null
            ],
            [
                "os" => "ios",
                "os_version" => "5.1",
                "browser" => "Mobile Safari",
                "device" => "iPhone 4S",
                "browser_version" => null
            ],
            [
                "os" => "ios",
                "os_version" => "8.3",
                "browser" => "Mobile Safari",
                "device" => "iPad Air",
                "browser_version" => null
            ],
            [
                "os" => "android",
                "os_version" => "5.0",
                "browser" => "Android Browser",
                "device" => "Google Nexus 6",
                "browser_version" => null
            ],
        ];

        $options = [
            'url' => $url,
            'browsers' => $browsers
        ];

        $response = $this->sendRequest('POST', $options);
        $response = json_decode($response);
        $output = [];

        if ($response !== NULL && !is_string($response) && !isset($response->error) && isset($response->job_id)) {
            $output['status'] = 'success';
            $output['response'] = $this->getListOfScreenshots($response->job_id);
            return $output;
        } else {
            $output['status'] = 'failed';
            $output['response'] = $response;
            return $output;
        }
    }

    private function getListOfScreenshots($jobId)
    {
        $screenshots = [];

        $tries = 0;
        do {
            sleep(2);
            $response = $this->sendRequest('GET', '/' . $jobId . '.json');
            $response = json_decode($response);

            if ($response->state == "done") {
                $screenshot = [];
                foreach ($response->screenshots as $test) {
                    $screenshot['os'] = $test->os . " " . $test->os_version;

                    if (isset($test->device)) {
                        $screenshot['device'] = $test->device;
                    } else {
                        $screenshot['device'] = "";
                    }

                    $screenshot['browser'] = $test->browser . " " . $test->browser_version;
                    $screenshot['img'] = $test->image_url;
                    $screenshots[] = $screenshot;
                }
            }
            $tries++;
        } while ($response->state != "done"); // && $tries < 40

        if (empty($screenshots)) {
            $screenshots['status'] = 'failed';
            $screenshots['response'] = 'Browserstack timeout of failure';
        }

        return $screenshots;
    }

    private function sendRequest($method, $options)
    {
        $encodedAuth = base64_encode($this->username . ":" . $this->accessKey);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_ENCODING => "",
            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER, ["Authorization : Basic " . $encodedAuth]
        ));

        if ($method === 'GET') {
            curl_setopt($curl, CURLOPT_URL, $this->apiUrl . $options);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        }

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_URL, $this->apiUrl);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($options));
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                "content-type: application/json",
                "Authorization : Basic " . $encodedAuth
            ]);
        }

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            return $response;
        }
    }
}