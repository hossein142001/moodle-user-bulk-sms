<?php
/**
 * Created by PhpStorm.
 * User: h.abaei
 * Date: 17/12/2016
 * Time: 08:52 AM
 */


class sms {

    var $client;
    var $userName;
    var $password ;


    /**
     * Outputs ans lists all news grouped by type
     *
     * @return    void
     */
    function GetSql($smsid = null) {

        global $CFG , $DB , $USER;

        $where = $smsid ? " AND sms.id=$smsid" : "";

        $sql = "SELECT 
              *
            FROM 
              {sms} AS sms
              INNER JOIN {user} AS touser ON touser.id = sms.to_userid 
              INNER JOIN {user} AS user ON user.id = sms.userid 
            WHERE  
              $where
            ORDER BY  sms.timecreated DESC";

        $news = $DB->get_records_sql($sql);

    }


    function sms()
    {

        $this->client = new SoapClient('http://www.payamsms.com/wsdl?t=' . time(), array(
            'location' => 'http://www.payamsms.com/soap',
            'uri' => 'http://www.payamsms.com/soap',
            'use' => SOAP_LITERAL,
            'style' => SOAP_DOCUMENT,
            'trace' => 1
        ));

        $this->userName = get_config('local_sms' , 'userName');
        $this->password = get_config('local_sms' , 'password');

    }

    /**
     * Getting customer's balance...
     *
     * @return mixed
     */
    function get_customer_balance(){


        //Get customer's balance
        $response = $this->client->getBalance(array(
            'userName'=>$this->userName,
            'password' =>$this->password,
            'facilityId' => 1
        ));

        return $response->getBalanceReturn->balance;
    }



    /**
     * Getting status of a message...
     *
     * @param $messageId
     * @return mixed
     */
    function get_message_status($messageId){


        //Get message status
        $response = $this->client->getStatus(array('messageId'=>$messageId));

        return $response->getStatusReturn;
    }


    /**
     * Sending message...
     *
     * @param array $numbers
     * @param string $content
     * @return bool
     */
    function send_message( array $numbers , $content){

        //Sending message
        $response = $this->client->send(array(
            //'userName'=>'ClJSE12ssUm-wSQgbOWjYw',
            'userName'=>$this->userName,
            //'password' =>'helalrms03',
            'password' =>$this->password,
            'shortNumber' =>'2000147',
            'sourceNo' => '2000147',
            'destNo'      => $numbers,
            'sourcePort'  => 0,
            'destPort'    => 0,
            'clientId'    => null,
            'messageType' => 1,
            'encoding'    => 2,
            'longSupported' => false,
            'dueTime'     => null,
            'content'     => $content
        ));

        if ($response->sendReturn->status == 0) {
            return $response->sendReturn->id->id ;
        } else {
            return false ;//;"Error while sending message(s). Errno {$response->sendReturn->status} \n";
        }
    }


    /**
     * checking sent message status
     *
     * @param array $ids
     * @return array|bool
     */
    function get_send_message_status( array $ids )
    {

        //checking sent message status
        $response = $this->client->statusReport(array(
            'userName' => $this->userName,
            'password' => $this->password,
            'ids' => array(3989120000000, 4989120000000),
            'clientIds' => null
        ));
        if ($response->statusReportReturn->status == 0) {
            if (empty($response->statusReportReturn->report) || empty($response->statusReportReturn->report->report)) {
                //Invalid message id(s)
                return false;
            } else {
                $out = [];
                if (is_array($response->statusReportReturn->report->report)) {
                    foreach ($response->statusReportReturn->report->report as $report) {
                        $out [$report->id ]= $report->status;
                    }
                } else {
                    $out [$response->statusReportReturn->report->report->id]=$response->statusReportReturn->report->report->status ;
                }
            }
            return $out;
        } else {
            //Error while receiving status report. Errno {$response->statusReportReturn->status}
            return false;
        }
    }
}