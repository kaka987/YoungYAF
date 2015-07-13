<?php
class Action_NumberOfNode extends Controller_Application
{

	protected $isNeedLogin = User_Session::LOGIN_WITHOUT;
	
	public function run(){
		
		$beginTime = time() - 120;

		$result['series'] = $this->getReqOfNode($beginTime);

        ksort($result['series']);

        if($result['series']){
            static::output(1,'NumberOfNode', $result);
        }else{
            static::output(0, 'no data', array());
        }
	}

    /**
     * 获取节点访问次数
     *
     * @param $beginTime
     * @return array
     */
    public function getNumberOfNode($beginTime)
    {
        $mongoDao = new Dao_Mongo('accesslog');
        $returns = array();

        $collections = $mongoDao->db->getCollectionNames();

        $colSize = count($collections);
        for ($i = 0; $i < $colSize; $i++) {
            $mongoDao->whereGte("time", new MongoDate($beginTime));
            $mongoDao->whereLt("time", new MongoDate($beginTime + 60));

            $returns[$collections[$i]] = $mongoDao->count($collections[$i]);
        }

        return $returns;
    }

    public function getReqOfNode($beginTime=NULL) {

        $output = array();
        $monitorDataNodeService = new Model_Alarm_DataNode();
        return $monitorDataNodeService->getNodeData();
    }
}