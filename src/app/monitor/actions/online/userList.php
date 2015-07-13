<?php
class Action_Userlist extends Controller_Application
{
    protected $layout = 'main';
    const PAGE_NUM = 10;

    public function run() {
        $serviceUserOnline = new Service_User_Online();

        $page  = $this->getRequest()->getQuery('page', 1);
        $email = trim($this->getRequest()->getQuery('kw'));

        $userOnline = array('email' => $email);
        $result = $serviceUserOnline->getList($userOnline, self::PAGE_NUM, $page);

        $count = $result['count'];
        $userList = $result['data'];

        $prevPage = $page - 1;

        if($prevPage < 1) {
            $prevPage = 1;
        }

        $nextPage = $page + 1;

        if($nextPage > $count) {
            $nextPage = $count;
        }

        $this->getView()->assign('prevPage', $prevPage);
        $this->getView()->assign('nextPage', $nextPage);
        $this->getView()->assign('page', $page);
        $this->getView()->assign('pageNum', $count);
        $this->getView()->assign('userList', $userList);
    }
}