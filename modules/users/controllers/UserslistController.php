<?

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Users_UserslistController extends Monkeys_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->viewRenderer->setNeverRender(true);

        $users = new Users();

        switch($this->_getParam('filter')) {
            case 'confirmed':
                $where = "accepted_eula=1 AND role != '".User::ROLE_ADMIN."'";
                break;
            case 'unconfirmed':
                $where = "accepted_eula=0 AND role != '".User::ROLE_ADMIN."'";
                break;
            default:
                $where = false;
                break;
        }

        $usersRows = $users->getUsers(
            $this->_getParam('startIndex'),
            $this->_getParam('results'),
            $this->_getParam('sort', 'registration'),
            $this->_getParam('dir', Users::DIR_DESC),
            $where);

        $jsonObj = new StdClass();
        $jsonObj->recordsReturned = count($usersRows);
        $jsonObj->totalRecords = $users->getNumUsers($where);
        $jsonObj->totalUnconfirmedUsers = $users->getNumUnconfirmedUsers();
        $jsonObj->startIndex = $_GET['startIndex'];
        $jsonObj->sort = $this->_getParam('sort');
        $jsonObj->dir = $this->_getParam('dir');
        $jsonObj->records = array();

        foreach ($usersRows as $user) {
            if ($user->role == User::ROLE_ADMIN) {
                $status = $this->view->translate('admin');
            } else if ($user->accepted_eula) {
                $status = $this->view->translate('confirmed');
            } else {
                $status = $this->view->translate('unconfirmed');
            }
            $jsonObjUser = new StdClass();
            $jsonObjUser->id = $user->id;
            $jsonObjUser->name = $user->getFullName();
            $jsonObjUser->registration = $user->registration_date;
            $jsonObjUser->role = $user->role;
            $jsonObjUser->status = $status;
            $jsonObj->records[] = $jsonObjUser;
        }

        echo Zend_Json::encode($jsonObj);
    }
}
