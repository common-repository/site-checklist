<?php
//
//namespace VOCheck;
//
//class CrossBrowserTesting
//{
//    CONST BASE_URL = 'crossbrowsertesting.com/api/v3/screenshots';
//    private $username;
//    private $accessKey;
//
//    private $apiUrl;
//
//
//    public function __construct($username, $accessKey)
//    {
//        $this->username = urlencode($username);
//        $this->accessKey = urlencode($accessKey);
//        $this->apiUrl = 'https://' . $this->username . ":" . $this->accessKey . '@crossbrowsertesting.com/api/v3/screenshots';
//    }
//
//    public function getBrowsers()
//    {
//        return $this->sendRequest('GET', '/browsers');
//    }
//
//    public function generateScreenshots($url)
//    {
//        $browsers = [
////            "iPhone6-iOS8sim",
//            "IE9",
//            "IE10",
//        ];
//
//         $options = [
//            'url' => $url,
//            'format' => 'json',
//            'browser_list_name' => 'VOBrowsersTest'
//        ];
//
//        $response = $this->sendRequest('POST', $options);
//        $response = json_decode($response);
//        var_dump($response->screenshot_test_id);
//        var_dump($response);
//        die();
//        $job = 1994578;
//        //return
//        $this->getListOfScreenshots($job);
//        die();
////        return $options;
//    }
//
//    private function getListOfScreenshots($testId)
//    {
//        $screenshots = [];
//        do {
//            sleep(2);
//            $response = $this->sendRequest('GET', '/' . $testId);
//            $response = json_decode($response);
//            $results = $response->versions->results;
//            //foreach, image in array, return images.
//            echo $response->versions->result_count;
//            die();
//            foreach ($results as $result) {
//                var_dump($result);
////                if ($result->successful == true) {
////                    $screenshot = [];
////                    $screenshot['img'] = $result->images->chromeless;
////                    $screenshot['os'] = $result->os->name;
////                    $screenshot['browser'] = $result->browser->name;
////                    $screenshots[] = $screenshot;
////                }
//            }
//        } while ($response->versions->result_count != 0);
//
//        var_dump($screenshots);
//        die();
//        return $response;
//    }
//
//    private function sendRequest($method, $options)
//    {
//        $curl = curl_init();
//        curl_setopt_array($curl, array(
//            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_ENCODING => "",
////            CURLOPT_USERPWD => $this->username . ":" . $this->accessKey,
//            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 20,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_HTTPHEADER => array(
//                "cache-control: no-cache",
//            ),
//        ));
//
//        if ($method === 'GET') {
//            curl_setopt($curl, CURLOPT_URL, $this->apiUrl . $options);
//            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
//        }
//
//        if ($method === 'POST') {
//            curl_setopt($curl, CURLOPT_URL, $this->apiUrl);
//            curl_setopt($curl, CURLOPT_POSTFIELDS, $options);
//            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
//        }
//
//        $response = curl_exec($curl);
//        $err = curl_error($curl);
//
//        curl_close($curl);
//
//        if ($err) {
//            return $err;
//        } else {
//            return $response;
//        }
//    }
//}