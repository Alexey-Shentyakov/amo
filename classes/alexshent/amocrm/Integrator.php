<?php

namespace alexshent\amocrm;

class Integrator {
    
    private $api;
    
    public function __construct() {
        $this->api = [];
        $this->api['auth'] = new \alexshent\amocrm\api\Auth();
        $this->api['account'] = new \alexshent\amocrm\api\Account();
        $this->api['contact'] = new \alexshent\amocrm\api\Contact();
        $this->api['pipeline'] = new \alexshent\amocrm\api\Pipeline();
        $this->api['lead'] = new \alexshent\amocrm\api\Lead();
        $this->api['task'] = new \alexshent\amocrm\api\Task();
    }
    
    public function auth() {
        $result = $this->api['auth']->auth();
        return $result;
    }
    
    public function get_custom_fields_info() {
        $info = $this->api['account']->get_custom_fields_info();
        $custom_fields_contacts = $info['_embedded']['custom_fields']['contacts'];

        $custom_fields_info = new \stdClass();
        $custom_fields_info->work_telephone = ['id' => null, 'enum' => null];
        $custom_fields_info->work_email = ['id' => null, 'enum' => null];

        foreach ($custom_fields_contacts as $cfc) {
            
            // work telephone
            if ($cfc['name'] === 'Телефон') {
                $custom_fields_info->work_telephone['id'] = $cfc['id'];
                
                foreach ($cfc['enums'] as $k => $e) {
                    if ($e === 'WORK') {
                        $custom_fields_info->work_telephone['enum'] = $k;
                        break;
                    }
                }
            }
            
            // work email
            if ($cfc['name'] === 'Email') {
                $custom_fields_info->work_email['id'] = $cfc['id'];
                
                foreach ($cfc['enums'] as $k => $e) {
                    if ($e === 'WORK') {
                        $custom_fields_info->work_email['enum'] = $k;
                        break;
                    }
                }
            }
        }
        
        return $custom_fields_info;
    }
    
    public function get_pipeline_info() {
        $result = $this->api['pipeline']->get();

        $first_pipeline = array_shift($result);
        $pipeline_id = $first_pipeline['id'];
        $status_id = 0;
        
        // get 'Первичный контакт' status id
        foreach ($first_pipeline['statuses'] as $status) {
            if ($status['name'] === 'Первичный контакт') {
                $status_id = $status['id'];
                break;
            }
        }
        
        $info = new \stdClass();
        $info->pipeline_id = $pipeline_id;
        $info->status_id = $status_id;
        
        return $info;
    }
    
    public function get_admin_users() {
        $admin_users = [];
        $users = $this->api['account']->get_users_info();
        
        foreach ($users as $u) {
            if ($u['is_admin'] === 1) {
                $admin_users[] = $u['id'];
            }
        }
        
        return $admin_users;
    }
    
    /*
    Если же контакт не был найден, то ответственный выбирается по принципу
    равномерного распределения сделок между пользователями за текущие сутки
    (считаем все сделки за текущие сутки для каждого пользователя CRM и ставим ответственным того, у кого наименьшее количество сделок).
    
    Количество сделок за текущие сутки у которых один и тот же контакт считать как одна сделка.
    
    Администратор (владелец аккаунта) не участвует в распределении сделок от новых контактов.
    */
    public function equal_distribution_user_id() {
        
        $today = getdate();
        $format = $today['year'] . '-' . $today['mon'] . '-' . $today['mday'] . ' ' . '00-00-00';
        $date = \DateTime::createFromFormat('Y-m-d H-i-s', $format);
        $from = $date->getTimestamp();
        
        $to = time();

        // get today leads        
        $today_leads = $this->api['lead']->get_filter_creation_date($from, $to);
        
        //echo "today leads\n";
        //print_r($today_leads);
        
        $counter = [];
        $admin_users = $this->get_admin_users();
        
        foreach ($today_leads as $tl) {
            $tl_user_id = $tl['responsible_user_id'];
            $tl_contact_id = $tl['main_contact']['id'];
            
            // ignore admins
            if (!in_array($tl_user_id, $admin_users)) {
            
                if (isset($counter[$tl_user_id][$tl_contact_id])) {
                    $counter[$tl_user_id][$tl_contact_id] ++;
                }
                else {
                    $counter[$tl_user_id][$tl_contact_id] = 1;
                }
            }
        }
        
        //print_r($counter);
        
        $min = count(current($counter));
        $min_user_id = key($counter);
        foreach ($counter as $uid => $c) {
            if (count($c) < $min) {
                $min = count($c);
                $min_user_id = $uid;
            }
        }
        
        //echo "min = $min\n";
        //echo "min_user_id = $min_user_id\n";
        
        return $min_user_id;
    }
    
    public function contact_search_by_email($email) {
        $contact = $this->api['contact']->get_by_email($email);
        
        return $contact;
    }
    
    public function contact_search_by_telnum($telnum) {
        $contact = $this->api['contact']->get_by_telnum($telnum);
        
        return $contact;
    }
    
    public function create_new_contact($name, $telnum, $email) {
        $custom_fields_info = $this->get_custom_fields_info();
        
        $contact_new = $this->api['contact']->add(
            [
                [
                    'name' => $name,
                    'custom_fields' => [
                        [
                            'id' => $custom_fields_info->work_telephone['id'],
                            'values' => [
                                [
                                    'value' => $telnum,
                                    'enum' => $custom_fields_info->work_telephone['enum']
                                ]
                            ]
                        ],
                        
                        [
                            'id' => $custom_fields_info->work_email['id'],
                            'values' => [
                                [
                                    'value' => $email,
                                    'enum' => $custom_fields_info->work_email['enum']
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );
        
        $contact_id = $contact_new[0]['id'];
        
        return $contact_id;
    }
    
    public function create_new_lead($user_id, $contact_id) {
        // get pipeline info
        $pipeline_info = $this->get_pipeline_info();
        
        $lead_new = $this->api['lead']->add(
            [
                [
                    'name' => 'Заявка с сайта',
                    'pipeline_id' => $pipeline_info->pipeline_id,
                    'status_id' => $pipeline_info->status_id,
                    'responsible_user_id' => $user_id,
                    'contacts_id' => [
                                        $contact_id
                                    ]
                ]
            ]
        );
        
        $lead_id = $lead_new[0]['id'];
        
        return $lead_id;
    }
    
    /*
    задача с типом “Перезвонить клиенту”
    ответственный у задачи должен быть такой же как и у сделки, а срок выполнения задачи 1 день;
    */

    public function create_new_task($user_id, $text, $lead_id) {
        
        $today = getdate();
        $format = $today['year'] . '-' . $today['mon'] . '-' . ($today['mday']+1) . ' ' . '00-00-00';
        $date = \DateTime::createFromFormat('Y-m-d H-i-s', $format);
        $timestamp = $date->getTimestamp();
        
        $task_new = $this->api['task']->add(
            [
                [
                    'responsible_user_id' => $user_id,
                    'text' => $text,
                    'task_type' => 1,
                    'complete_till_at' => $timestamp,
                    'element_type' => 2,
                    'element_id' => $lead_id
                ]
            ]
        );
        
        $task_id = $task_new[0]['id'];
        
        return $task_id;
    }
}
