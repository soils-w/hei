<?php

namespace app\model;

use think\Model;

class Base extends Model
{
    protected $table = '';

    /**
     * 获取指定字段
     * @param $where
     * @param $field
     *
     * @return mixed 返回字符串
     */
    public function getField($where, $field, $order = 'id desc')
    {
        $content = $this->where($where)->order($order)->value($field);
        return $content;
    }

    /**
     * 返回一列数据
     * @param $id int ID
     *@field字段名，只能传一个字段
     * @return mixed 返回字符串
     */
    public function getColumn($where, $field, $type=1,$limit=0)
    {
        if($type==1){
            // 返回数组
            $rs = $this->where($where);
            if($limit) {
                $rs = $rs->limit($limit);
            }
            return $rs->column($field);
        }else{
            $rs = $this->where($where);
            if($limit) {
                $rs = $rs->limit($limit);
            }
            // 指定id字段的值作为索引
            return $rs->column($field, 'id');
        }
    }
    /**
     * 获取总数
     */
    public function getSum($where,$field='')
    {
        $rs = $this->where($where)->sum($field);
        return $rs;
    }
    /**
     * 获取最大值
     */
    public function getMax($where,$field='')
    {
        $rs = $this->where($where)->max($field);
        return $rs;
    }
    /**
     * 获取最小值
     */
    public function getMin($where,$field='')
    {
        $rs = $this->where($where)->min($field);
        return $rs;
    }
    /**
     * 统计数量
     */
    public function getCount($where)
    {
        $rs = $this->where($where)->count();
        return $rs;
    }

    /**
     * 返回一组数据
     * @param $where
     * @param $field 查询字段
     * @param $order 排序条件【字段，desc/asc】
     */
    public function getList($where,$field='',$order='id desc')
    {
        $data = array();
        $data['list']= [];
        $data['total'] = 0;
        $rs = $this->where($where)->field($field);
        $total = $rs->count();
        if(!empty($order))
        {
            $rs = $rs->order($order);
        }
        $rs = $rs->select();

        if(!empty($rs))
        {
            $data['list'] = $rs->toArray();
        }else{
            $data['list'] = [];
        }
        $data['total'] = $total;

        return $data;
    }

    /**
     * 返回一组数据
     * @param $where
     * @param $field 查询字段
     * @param  $pagearr分页条件【页码，每页行数】不用自行计算limit，直接传递页码
     * @param $order 排序条件【字段，desc/asc】
     */
    public function getPageList($where,$field='',$page,$pagesize,$order='id desc',$whereor=[])
    {
        $data = array();
        $data['list']= [];
        $data['page'] = $page;
        $data['pages'] = 0;
        $data['pagesize'] = $pagesize;
        $data['total'] = 0;

        $rs = $this->where($where)->whereOr($whereor)->field($field);
        $total = $rs->count();
        $data['pages'] = ceil($total/$pagesize);

        if($page && $pagesize)
        {
            //防止出现当前页数大于实际页数没有数据的情况
            if ($page > $data['pages']) {
                $rs = $rs->page($data['pages'],$pagesize);
            } else {
                $rs = $rs->page($page,$pagesize);
            }
        }
        if(!empty($order))
        {
            $rs = $rs->order($order);
        } else {
            $rs = $rs->orderRand();
        }
        $rs = $rs->select();

        if(!empty($rs))
        {
            $data['list'] = $rs->toArray();
        }else{
            $data['list'] = [];
        }

        $data['total'] = $total;
        return $data;
    }

    /**
     * 获取详情
     * @param $id int ID
     *
     * @return mixed 返回数组
     */
    public function getDetail($where,$field,$order="id desc")
    {
        $relation = array();
        $detail = $this->where($where)->field($field)->relation($relation)->order($order)->find();

        if(!empty($detail)) {
            return $detail->toArray();
        }
        else{
            return [];
        }

    }
    /**
     * 物理删除
     */
    public function del($where)
    {
        return $this->destroy(function($query)use ($where){
            $query->where($where);
        },1);
    }
    /**
     * 软删除
     */
    public function softDelete($where)
    {
        return $this->destroy(function($query)use ($where){
            $query->where($where);
        });

    }

    /**
     * 修改信息
     * @param int $id
     * @param array $data
     * @return array 返回数组
     */
    public function modify($data)
    {
        $res = $this->update($data);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    /**
     * 没有主键的情况下修改信息
     * @param int
     * @param array $data
     * @return array 返回数组
     */
    public function modify_where($data,$where)
    {
        $res = $this->update($data,$where);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    /**
     * 添加信息
     * @param $data
     * @return array 返回数组
     */
    public function add($data)
    {
        $res = $this->insertGetId($data);
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

}