<?php
/**
 * @package       Plugin User Login Cop for Joomla! 3.6
 * @author        Massimo Di Primio - http://www.diprimio.com
 * @copyright (C) 2017 - Massimo Di Primio
 * @license       GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 **/

//-- No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');
/**
 * Class plgUserUserlogCop ###
 */
class plgUserLoginCop extends JPlugin   {
    protected $db;                          # Database Object. Automatically assigned by parent constructor
    protected $app;                         # Application. Automatically assigned by parent constuctor
    protected $autoloadLanguage = true;     # when true, language files is loaded automatically.
    protected $action = 0;                  # 0 = Unsuccessful login, 1 = Successfull login
    protected $_app;                        # 
    protected $_ip;                         # 

    /*
     * Run the parent Constructor
     * so we do not forget or ignore anything that is run by jplugin 
     */
    public function __construct(&$subject, $options = array()) {
        parent::__construct($subject, $options);
        $this->db = JFactory::getDbo();                 // Get the global JDatabaseDriver object
        $this->_app = JFactory::getApplication();
    }
    
    /**
     * Joomla! trigger function acting after a user is successfully logged in.
     * @param   array $options Array holding options
     * @return  void
     * @since   1.0.0
     */
    public function onUserAfterLogin($options)  {
        if (!$this->params->get('lc_watch_admin') && $options['user']->get('isRoot'))  {
            return;
        }
        $this->action = 1;
        $this->manageLoginAction ($options);
        return;
    }
    /**
     * Joomla! trigger function acting after on Unsuccessful log in.
     * @param   array $options Array holding options
     * @return  void
     * @since   1.0.0
     */
    public function onUserLoginFailure($options)     {
        $this->action = 0;
        $this->manageLoginAction ($options);
        return;
    }
    /**
     * Joomla! trigger function acting after a user is successfully logged in.
     * @param   array $options Array holding options
     * @return  void
     * @since   1.0.0
     */
    public function manageLoginAction ($options)   {
        $loginRecord               = array();
        $loginRecord['action']     = $this->action;
        $loginRecord['userid']     = empty($options['user']->id)       ? -1        : $options['user']->id;
        $loginRecord['username']   = empty($options['user']->username) ? 'UNKNOWN' : $options['user']->username;
        $loginRecord['ip']         = $this->getIP();
        $loginRecord['time_login'] = JFactory::getDate();       # This must be in the format 'YYYY-MM-DD hh:mi:ss'

        # Store data into Database if needed
        if (intval($this->params->get('lc_db_enabled')) != 0) {
            if ($this->action == 1) {
                if ( (intval($this->params->get('db_rec_login_success')) != 0) )    {
                    $this->storeLoginData($loginRecord);
                }
            }
            if ($this->action == 0) {
                if ( (intval($this->params->get('db_rec_login_failure')) != 0) )    {
                    $this->storeLoginData($loginRecord);
                }
            }
        }
        
        # Send out email if needed
        if (intval($this->params->get('lc_send_mail')) != 0) {
            if ($this->action == 1) {
                if (intval($this->params->get('lc_email_login_success') != 0 )) {
                    $this->sendMailOut($loginRecord);
                }
            }
            if ($this->action == 0) {
                if (intval($this->params->get('lc_email_login_failure') != 0 )) {
                    $this->sendMailOut($loginRecord);
                }
            }
        }
        return;
    }

    /**
     * Stores the login data into the database
     * @param   array $data Array holding data to be stored.
     * @return  bool
     * @since   1.0.0
     */
    private function storeLoginData($data)  {
        $query = $this->db->getQuery(true);
        $dbcols = array('action','userid','username','ip','time_login');
        $dbvals = array($data['action'], $data['userid'], $this->db->quote($data['username']), $this->db->quote($data['ip']), $this->db->quote($data['time_login']));
        $query  ->insert($this->db->quoteName('#__user_login_cop'))
                ->columns($this->db->quoteName($dbcols))
                ->values(implode(',', $dbvals));
        //
        $this->db->setQuery($query);
        try {
            $this->db->execute();
        }
        catch (Exception $e)    {
            return false;   #throw($e);
        }
        
        // finally clean up oldest database records
        $this->cleanUpOldestRecords();
        
        return true;
    }
    
    /**
     * Cleanup oldest database records as established by 'lc_db_daylimit' or 'lc_db_recslimit' config parameters
     * @param   None
     * @return  void
     * @since   version 1.0.0
     */
    private function cleanUpOldestRecords() {
        if (intval($this->params->get('lc_db_daylimit')) > 0)   {
            // Always trust in php date and keep using it, because mysl date may differ
            $date_cut = JFactory::getDate('now -'.intval($this->params->get('lc_db_daylimit')).' day');    # in the format: '%Y-%m-%d %H:%i-%s'
            
            // DELETE FROM #__table WHERE time_login < $date_cut);
            $query = "DELETE FROM #__user_login_cop WHERE time_login < '" . $date_cut . "'";
            $this->db->setQuery($query);
        }
        else if (intval($this->params->get('lc_db_recslimit')) > 0 && intval($this->params->get('lc_db_daylimit')) == 0)   {
            // Note:
            // DELETE FROM #__table ORDER BY id ASC LIMIT 3;                                                 # Not working correctly in mysql
            // DELETE FROM vykg9_user_login_cop WHERE id < (SELECT (MAX(id)-111) FROM vykg9_user_login_cop); # Mysql ERROR 1093
            // DELETE FROM #__table WHERE id < (SELECT * FROM (SELECT (MAX(id)-N) FROM tablename ) AS maxid) # This works!
            $query = 
                    'DELETE FROM #__user_login_cop WHERE id < (SELECT * FROM (SELECT (MAX(id)-'.intval($this->params->get('lc_db_recslimit')-1).') FROM #__user_login_cop) AS a)';
            $this->db->setQuery($query);
            
        }
        // Try to ececute the query statement
        if (!empty($query))   {
            try {
                $this->db->execute();
            }
            catch (Exception $e)    {
                return false;   #throw($e);
            } 
        }
        return;
    }

    /**
     * Sends out an email to all members of the selected user group
     * @param   array $data Array holding data to be emailed.
     * @return  void
     * @since   1.0.0
     */
    private function sendMailOut($data) {
        if ( intval($this->params->get('lc_send_mail')) == 0 )  { #|| intval($this->params->get('lc_usergroup') )   {
            return;
        }
                
        // Get recipients in the group
        $recipients = JAccess::getUsersByGroup($this->params->get('lc_usergroup'));

        if (empty($recipients))    { 
            return; 
        }
        // Grab all 'email' and 'name' from table 'users' which are allowed to receive emails
        #$this->db = JFactory::getDbo();
        $query = $this->db->getQuery(true);
        $query
                ->select($this->db->quoteName(array('email', 'name')))
                ->from($this->db->quoteName('#__users'))
                ->where($this->db->quoteName('sendEmail') . ' = 1')
                ->where($this->db->quoteName('id') . ' IN (' . implode(',', $recipients) . ')');
        $this->db->setQuery($query);

        $recipients = $this->db->loadObjectList();

        if (empty($recipients))    {
            return;
        }
       
        $eol = intval($this->params->get('lc_mailformat')) ? "<br />" : "\n";   #lc_mailformat
        $subject = JText::_('PLG_LOGINCOP_EMAIL_SUBJECT');
        $body    = JText::_('PLG_LOGINCOP_EMAIL_HEADER') . $eol;

        if (intval($this->params->get('lc_mailbody_usernme')))    { $body .= "Username:   " . $data['username'] . $eol;}
        if (intval($this->params->get('lc_mailbody_userid')))     { $body .= "User ID:    " . $data['userid'] . $eol;}
        if (intval($this->params->get('lc_mailbody_login_time'))) { $body .= "Date Time:  " . $data['time_login'] . $eol;}
        if (intval($this->params->get('lc_mailbody_ipaddress')))  { $body .= "IP Address: " . $data['ip'] . $eol;}

        $mailer = JFactory::getMailer();
        foreach ($recipients as $recipient) {
            $mailer->addRecipient($recipient->email, $recipient->name);
        }
        $mailer->setSender(array($this->app->get('mailfrom'), $this->app->get('fromname')));
        $mailer->setSubject($subject);
        $mailer->setBody($body);
        $mailer->isHtml(intval($this->params->get('lc_mailformat')));      #$mailer->isHtml(true);
        $mailer->Send();

        return;
    }
    
    /**
     * Retrieve the client IP Address
     * @param   void
     * @return  void
     * @since   1.0.0
     */
    private function getIP() {
        $jinput = JFactory::getApplication()->input;
        $ip = $jinput->server->getString('HTTP_CLIENT_IP');
        if (!$ip) { 
            $ip = $jinput->server->getString('HTTP_X_FORWARDED_FOR'); 
            if (!$ip) { 
                $ip = $jinput->server->getString('HTTP_X_FORWARDED');
                if (!$ip) { 
                    $ip = $jinput->server->getString('HTTP_X_FORWARDED_FOR');
                    if (!$ip) { 
                        $ip = $jinput->server->getString('HTTP_FORWARDED_FOR');
                        if (!$ip) { 
                            $ip = $jinput->server->getString('HTTP_FORWARDED');
                            if (!$ip) { 
                                $ip = $jinput->server->getString('REMOTE_ADDR');
                            }
                        }
                    }
                }
            }
        }
        return $ip;
    }
}
