<?php 

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('send_sms_form.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/user/lib.php');

require_once($CFG->dirroot.'/message/lib.php');

$context = get_context_instance(CONTEXT_SYSTEM);
require_capability('local/sms:send_message',$context );

$url = new moodle_url('/local/sms/index.php');
$PAGE->set_url($url, array()); // Defined here to avoid notices on errors etc
$PAGE->set_pagelayout('admin');
$PAGE->set_context( $context );
$PAGE->set_title( get_string('pluginname' , 'local_sms') );
$PAGE->set_heading( get_string('pluginname' , 'local_sms') );
// create the user filter form
$ufiltering = new user_filtering();

if (!empty($SESSION->bulk_users) && count($SESSION->bulk_users)>0 && !empty($_POST['submitbutton'])){

    // To send a mail if sendmail is checked
    $from = $DB->get_record('user', array('id' => $USER->id));
    $smsform = new user_sms_form('send_sms.php');

    if ($smsform->is_cancelled()) {
        redirect($CFG->wwwroot);

    // If data submitted insert to DB
    } else if ($sms_form = $smsform->get_data()) {

        if (!confirm_sesskey()) {
            print_error('Wrong sesskey');
        }

        // News to DB
        $newsms = new stdClass();
        $newsms->text = $sms_form->text;
        $newsms->userid = $USER->id;
        $newsms->status = 0;
        $newsms->timecreated = time();

        $sms = new sms();
        foreach ($SESSION->bulk_users as $userid) {
            $user = $DB->get_record("user", array("id" => $userid));
            if (!empty($user->phone2)) {
                $newsms->to_userid = $userid;
                $newsms->mobile = $user->phone2;
                $smsid = $DB->insert_record('sms', $newsms);
                $sms->send_message( [$user->phone2] , $sms_form->text);
            }
        }

        //check_sms_status();
        redirect($url , get_string("sms_sent" , "local_sms"), 2);
    }
    echo $OUTPUT->header();
    $smsform->display();
}else{
    echo $OUTPUT->header();
    // reset the form selections
    $SESSION->bulk_users = array();

    $user_bulk_form = new user_bulk_new_form(null, get_selection_data($ufiltering));

    if ($data = $user_bulk_form->get_data()) {
        if (!empty($data->addall)) {
            add_selection_all($ufiltering);

        } else if (!empty($data->addsel)) {
            if (!empty($data->ausers)) {
                if (in_array(0, $data->ausers)) {
                    add_selection_all($ufiltering);
                } else {
                    foreach($data->ausers as $userid) {
                        if ($userid == -1) {
                            continue;
                        }
                        if (!isset($SESSION->bulk_users[$userid])) {
                            $SESSION->bulk_users[$userid] = $userid;
                        }
                    }
                }
            }

        } else if (!empty($data->removeall)) {
            $SESSION->bulk_users= array();

        } else if (!empty($data->removesel)) {
            if (!empty($data->susers)) {
                if (in_array(0, $data->susers)) {
                    $SESSION->bulk_users= array();
                } else {
                    foreach($data->susers as $userid) {
                        if ($userid == -1) {
                            continue;
                        }
                        unset($SESSION->bulk_users[$userid]);
                    }
                }
            }
        }

        // reset the form selections
        unset($_POST);
        $user_bulk_form = new user_bulk_new_form(null, get_selection_data($ufiltering));
    }

    $ufiltering->display_add();
    $ufiltering->display_active();

    $user_bulk_form->display();
}

//admin_externalpage_print_footer();
echo $OUTPUT->footer();

?>