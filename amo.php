<?php

spl_autoload_register(
	function ($class) {
		$root = __DIR__;
		$file = $root . '/classes/' . str_replace('\\', '/', $class) . '.php';
		//echo "$file\n";
		if (is_readable($file)) {
			require $file;
		}
	}
);

// -------------------------

//print_r($_POST);
if (!empty($_POST['name'])) {
    $contact_name = $_POST['name'];
}

if (!empty($_POST['telnum'])) {
    $contact_telnum = $_POST['telnum'];
}

if (!empty($_POST['email'])) {
    $contact_email = $_POST['email'];
}

//$contact_name = 'ololo';
//$contact_telnum = '19597';
//$contact_email = '19597@mailinator.com';

// -------------------------

$integrator = new \alexshent\amocrm\Integrator();

// auth
$auth_ok = $integrator->auth();

if (!$auth_ok) {
    die("auth failed\n");
}

// contact search by email
$contact_by_email = $integrator->contact_search_by_email($contact_email);

if (!empty($contact_by_email)) {
    // contact found by email
    $responsible_user_id = $contact_by_email[0]['responsible_user_id'];
    $contact_id = $contact_by_email[0]['id'];
}
else {
    // contact not found, search by telephone number
    $contact_by_telnum = $integrator->contact_search_by_telnum($contact_telnum);
    
    if (!empty($contact_by_telnum)) {
        // contact found by telephone number
        $responsible_user_id = $contact_by_telnum[0]['responsible_user_id'];
        $contact_id = $contact_by_telnum[0]['id'];
    }
    else {
        // not found by email
        // not found by telephone number
        // create new contact

        $contact_id = $integrator->create_new_contact($contact_name, $contact_telnum, $contact_email);
        
        // equal distribution
        $responsible_user_id = $integrator->equal_distribution_user_id();
    }
}

// create lead
$new_lead_id = $integrator->create_new_lead($responsible_user_id, $contact_id);

// create task
$new_task_id = $integrator->create_new_task($responsible_user_id, 'Перезвонить клиенту', $new_lead_id);

echo "contact id = $contact_id";
echo "<br>";

echo "lead id = $new_lead_id";
echo "<br>";

echo "task id = $new_task_id";
echo "<br>";
