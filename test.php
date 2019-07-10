<?php


    /**
     * 服务-数据分析 - 徽商运营 - 基础数据
     * @return \think\Response
     */
    public function wsOperationBasic()
    {
        $done = 2; //运营状态(0未添加1已添加2完成）
        $countField = ['done_time', 'allot_time','add_time', 'status'];
        $date = $this->timestamp->getTimeStamp(); // 时间戳
        $total = [];

        try {

            // 今日客户量
            $where_today1[] = ['allot_time', 'between time', $date['today']];
            $where_today2[] = ['allot_time', 'between time', $date['today_qoq']];
            $total['today_allow'] = [
                'current' => $this->s_ws_operation->getCount($where_today1, $countField),
                'qoq' => $this->s_ws_operation->getCount($where_today2, $countField),
            ];

            // 本月客户量
            $where_month[] = ['allot_time', 'between time', $date['month']];
            $where_last_month[] = ['allot_time', 'between time', $date['month_qoq']];
            $total['month_allow'] = [
                'current' => $this->s_ws_operation->getCount($where_month, $countField),
                'qoq' => $this->s_ws_operation->getCount($where_last_month, $countField),
            ];

            // 今日完成
            $where_today_deal1 = [['done_time', 'between time', $date['today']], ['status', 'eq', $done]];
            $where_today_deal2 = [['done_time', 'between time', $date['today_qoq']], ['status', 'eq', $done]];
            $total['today_deal'] = [
                'current' => $this->s_ws_operation->getCount($where_today_deal1, $countField),
                'qoq' => $this->s_ws_operation->getCount($where_today_deal2, $countField),
            ];
            // 本月完成
            $where_month_deal1 = [['done_time', 'between time', $date['month']], ['status', 'eq', $done]];
            $where_month_deal2 = [['done_time', 'between time', $date['month_qoq']], ['status', 'eq', $done]];
            $total['month_deal'] = [
                'current' => $this->s_ws_operation->getCount($where_month_deal1, $countField),
                'qoq' => $this->s_ws_operation->getCount($where_month_deal2, $countField),
            ];

            // 今日添加
            $where_today_add1 = [['add_time', 'between time', $date['today']]];
            $total['today_add'] = [
                'current' => $this->s_ws_operation->getCount($where_today_add1, $countField),
            ];
            // 本月添加
            $where_month_add1 = [['add_time', 'between time', $date['month']]];
            $total['month_add'] = [
                'current' => $this->s_ws_operation->getCount($where_month_add1, $countField),
            ];

            // 完成率
            $total['today_deal']['percentage'] = $this->timestamp->closingRatio($total['today_deal']['current'] , $total['today_add']['current']);
            $total['month_deal']['percentage'] = $this->timestamp->closingRatio($total['month_deal']['current'] , $total['month_add']['current']);

            return ResponseMsg::showSuccessMsg('success', $total);

        } catch (\Exception $e) {
            return ResponseMsg::showExceptionMsg(400, $e->getMessage(), [$this->s_ws_operation->getLastSql()]);
        }
    }

    /**
     * 服务-数据分析 - 徽商运营 - 分配客户量
     * @param Request $request
     * @return \think\Response
     */
    public function wsOperationNumberOfCustomers(Request $request){

        $done = 2; //运营状态(0未添加1已添加2完成）
        $param = $request->param();
        $field = ['allot_time', 'done_time', 'status'];
        $dataProvider = [];

        // 判断筛选时间戳
        $timestampInfo = $this->timestamp->getTimestampBySearch($param);

        if (400 == $timestampInfo['code']) {
            return ResponseMsg::showErrorMsg(400, $timestampInfo['msg']);
        }
        $date = $timestampInfo['period'];         //开始-结束格式化时间
        $timeStamp = $timestampInfo['timeStamp']; //开始-结束时间戳
        $method = $timestampInfo['method'];       //展示方式 日/周/月/年

        // 获得时间坐标
        $coord = $this->timestamp->getDateByTimeInterval($method, $timeStamp);

        try {
            //客户状态 ：all 全部， done 完成 ,not_done 未完成， problem 问题客户
            if(isset($param['status']) && in_array($param['status'], ['done', 'not_done'])) {
                // '新零售设计进度：0未开始；1已添加（进行中）；2提交审核（审核中）；3已完成；4问题客户';
                $status = [
                    'done' => ['status', 'eq', $done],
                    'not_done' => ['status', 'neq', $done],
                ];
                $where_date[] = $status[$param['status']];
            }
            $where_date[] = ['allot_time', 'between time', $timeStamp];
            $listData = $this->s_ws_operation->getListAll($where_date, $field);
            // 计算结果赋给时间坐标
            $identification = ['today' => 'G', 'year' => 'Y/m', 'week' => 'n/j', 'month' => 'n/j'];

            if (isset($method) && in_array($method, ['today', 'week', 'month', 'year'])) {
                foreach ($listData as $list_v) {
                    $formatTime = date($identification[$method], strtotime($list_v['allot_time']));
                    isset($coord[$formatTime]) && $coord[$formatTime] += 1;
                }
                $dataProvider = [
                    'current' => $coord,
                    'total' => array_sum($coord),
                    'start_time' =>$date[0],
                    'end_time' =>$date[1],
                ];
            }
            return ResponseMsg::showSuccessMsg('success', $dataProvider);

        } catch (\Exception $e) {
            return ResponseMsg::showExceptionMsg(400, $e->getMessage(), []);
        }
    }

    /**
     * 服务-数据分析 - 徽商运营 - 完成量
     * @param Request $request
     * @return \think\Response
     */
    public function wsOperationNumberOfDone(Request $request){

        $param = $request->param();
        $field = ['done_time', 'status'];
        $dataProvider = [];
        $done = 2; //运营状态(0未添加1已添加2完成）

        // 判断筛选时间戳
        $timestampInfo = $this->timestamp->getTimestampBySearch($param);

        if (400 == $timestampInfo['code']) {
            return ResponseMsg::showErrorMsg(400, $timestampInfo['msg']);
        }
        $date = $timestampInfo['period'];         //开始-结束格式化时间
        $timeStamp = $timestampInfo['timeStamp']; //开始-结束时间戳
        $method = $timestampInfo['method'];       //展示方式 日/周/月/年

        // 获得时间坐标
        $coord = $this->timestamp->getDateByTimeInterval($method, $timeStamp);

        try {
            // 计算数据
            $where_date[] = ['status', 'eq', $done];
            $where_date[] = ['done_time', 'between time', $timeStamp];

            $listData = $this->s_ws_operation->getListAll($where_date, $field);
            // 计算结果赋给时间坐标
            $identification = ['today' => 'G', 'year' => 'Y/m', 'week' => 'n/j', 'month' => 'n/j'];

            if (isset($method) && in_array($method, ['today', 'week', 'month', 'year'])) {
                foreach ($listData as $list_v) {
                    $formatTime = date($identification[$method], strtotime($list_v['done_time']));
                    isset($coord[$formatTime]) && $coord[$formatTime] += 1;
                }
                $dataProvider = [
                    'current' => $coord,
                    'total' => array_sum($coord),
                    'start_time' =>$date[0],
                    'end_time' =>$date[1],
                ];
            }

            return ResponseMsg::showSuccessMsg('success', $dataProvider);

        } catch (\Exception $e) {
            return ResponseMsg::showExceptionMsg(400, $e->getMessage(), []);
        }
    }
    /**
     *  服务-数据分析 - 徽商运营 - 任务进度
     * @param Request $request
     * @return \think\Response
     */
    public function wsOperationDoneData(Request $request)
    {
        $param = $request->param();
        $field = ['pdd_operation.user_id', 'pdd_operation.department_id', 'pdd_operation.status', 'pdd_operation.allot_time', 'pdd_operation.done_time'];
        $dataProvider = [];
        $done = 2; //运营状态(0未添加1已添加2完成）

        // 判断筛选时间戳
        $timestampInfo = $this->timestamp->getTimestampBySearch($param);
        if (400 == $timestampInfo['code']) {
            return ResponseMsg::showErrorMsg(400, $timestampInfo['msg']);
        }
        $date = $timestampInfo['period'];
        $timeStamp = $timestampInfo['timeStamp'];

        try {
            // 分配量, 完成数只统计分配时间内的数据
            $where_date[] = ['pdd_operation.allot_time', 'between time', $timeStamp];
            $cursorData = $this->s_ws_operation->getListAllWithJoin($where_date, $field);
            foreach ($cursorData as $cursor_k => $cursor_v) {
                $user_id = $cursor_v['user_id'];
                if (isset($dataProvider[$user_id]['allow_count'])) {
                    $dataProvider[$user_id]['allow_count'] += 1;    // 总数
                    if($cursor_v['status']['value'] == $done) {
                        $dataProvider[$user_id]['allow_done_count'] += 1;   // 完成数 +1
                    }else{
                        $dataProvider[$user_id]['allow_not_done_count'] += 1;   // 未完成数 +1
                    }
                }elseif(strlen($user_id)){
                    // 预定义默认参数
                    $dataProvider[$user_id] = [
                        'user_id' => $user_id,
                        'user_name' => $cursor_v['user_name'],
                        'department_id' => $cursor_v['department_id'],
                        'department_name' => $cursor_v['department_name'],
                        'current_done_count' => 0,
                        'allow_count' => 0,
                        'allow_not_done_count' => 0,
                        'allow_done_count' => 0,
                        'percentage' => 0,
                    ];
                }
            }

            // 完成量,统计完成时间在条件$timeStamp的数据
            $where_date2[] = ['pdd_operation.done_time', 'between time', $timeStamp];
            $cursorData = $this->s_ws_operation->getListAllWithJoin($where_date2, $field);
            foreach ($cursorData as $cursor_k => $cursor_v) {
                $user_id = $cursor_v['user_id'];
                if (isset($dataProvider[$user_id]['current_done_count'])) {
                    $dataProvider[$user_id]['current_done_count'] += 1;
                }elseif(strlen($user_id)){
                    // 预定义默认参数
                    $dataProvider[$user_id] = [
                        'user_id' => $user_id,
                        'user_name' => $cursor_v['user_name'],
                        'department_id' => $cursor_v['department_id'],
                        'department_name' => $cursor_v['department_name'],
                        'current_done_count' => 0,
                        'allow_count' => 0,
                        'allow_not_done_count' => 0,
                        'allow_done_count' => 0,
                        'percentage' => 0,
                    ];
                }
            }

            // 百分比
            foreach ($dataProvider as $data_k => $data_v){
                $dataProvider[$data_v['user_id']]['percentage'] = $this->timestamp->closingRatio($data_v['allow_done_count'], $data_v['allow_count']);
            }

            if (count($dataProvider) > 0) {
                $last_names = array_column($dataProvider, 'current_done_count');
                array_multisort($last_names, SORT_DESC, $dataProvider);

                $total['current'] = $dataProvider;
                $total['start_time'] = $date[0];
                $total['end_time'] = $date[1];

                return ResponseMsg::showSuccessMsg('success', $total);
            }

        } catch (\Exception $e) {
            return ResponseMsg::showExceptionMsg(400, $e->getMessage(), []);
        }
        return ResponseMsg::showErrorMsg(400, '暂无数据');
    }

    /**
     * 服务-数据分析 - 徽商运营 - 活动报名
     * @param Request $request
     * @return \think\Response
     */
    public function wsOperationActivitiesLog(Request $request)
    {
        $param = $request->param();
        $operation_status = 1; //运营状态(1活动2其他）
        $is_enroll = 2; //是否报名活动或其他(1未报名2已报名)
        $countField = ['operation_id', 'operation_status', 'create_time'];
        $dataProvider = [];

        // 判断筛选时间戳
        $timestampInfo = $this->timestamp->getTimestampBySearch($param);

        if (400 == $timestampInfo['code']) {
            return ResponseMsg::showErrorMsg(400, $timestampInfo['msg']);
        }
        $date = $timestampInfo['period'];         //开始-结束格式化时间
        $timeStamp = $timestampInfo['timeStamp']; //开始-结束时间戳

        try {

            //报名数量
            $where = ['allot_time', 'between time', $timeStamp];
            $where_today1 = [$where, ['is_enroll', 'eq', $is_enroll]];
            $where_today2 = [$where, ['is_enroll', 'neq', $is_enroll]];
            $dataProvider['number'] = [
               'enroll' => $this->s_ws_operation->getCount($where_today1),
                'not_enroll' => $this->s_ws_operation->getCount($where_today2),
            ];

            // 报名分类
            $where = ['create_time', 'between time', $timeStamp];
            $where_today1 = [$where, ['operation_status', 'eq', $operation_status]];
            $where_today2 = [$where, ['operation_status', 'neq', $operation_status]];
            $activity = $this->s_ws_operation_log->getListAll($where_today1, $countField);  // 活动
            $other = $this->s_ws_operation_log->getListAll($where_today2, $countField); // 其他
            $dataProvider['class'] = [
                'activity' => count(array_unique(array_column($activity, 'operation_id'))), // 活动总客户数,删除重复数据
                'other' => count(array_unique(array_column($other, 'operation_id'))),  // 其他总客户数，删除重复数据
            ];

            // 报名重复率
            $repeat = &$activity;
            $count = array_count_values(array_count_values(array_column($repeat, 'operation_id')))[1];
            $dataProvider['frequency'] = [
                'activity' => $count,   // 报名一次的客户
                'other' => count($repeat) - $count,   // 报名两次或以上的客户
            ];
            $total = [
                'current'  => $dataProvider,
                'start_time' =>$date[0],
                'end_time' =>$date[1],
            ];

            return ResponseMsg::showSuccessMsg('success', $total);

        } catch (\Exception $e) {
            return ResponseMsg::showExceptionMsg(400, $e->getMessage(), [$this->s_ws_operation_log->getLastSql()]);
        }
    }

    /**
     * 服务-数据分析 - 徽商运营 - 未完成
     * @param Request $request
     * @return \think\Response
     */
    public function wsOperationNotDone(Request $request){
        return ResponseMsg::showSuccessMsg('success', ['待确定']);

    }

