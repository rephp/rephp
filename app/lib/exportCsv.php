<?php

namespace app\lib;

/**
 * 分批实时导出CSV标准数据操作类
 * 范例如下：
 * $header_arr   = ['day_date'=>'日期', 'spu'=>'SPU', 'title'=>'标题'];
 * $csv_exporter = new exportCsv('测试下载文件名字', $header_arr, 1000);
 * $total_info   = ['day_date'=>'汇总', 'spu'=>'汇总999', 'title'=>'汇总测试标题'];
 * $csv_exporter->loadTotalInfo($total_info)->loadData($data_list1)->loadData($data_list2)->download();
 */
class exportCsv
{
    protected $fp;
    protected $header_arr = [];
    protected $max_export_number;

    /**
     * 初始化导出环境及对象
     * @param string $file_base_name    下载的文件主名-不含后缀
     * @param array  $header_arr        表头信息
     * @param int    $max_export_number 最大导出条数,0代表不限制
     * @return void
     */
    public function __construct($file_base_name, array $header_arr, $max_export_number = 20000)
    {
        empty($file_base_name) && $file_base_name = date('Y-m-d', time());

        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('default_socket_timeout', 1800);
        ini_set('memory_limit', '1024M');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_base_name . '.csv');
        header('Expires: 0');
        //打开php标准输出流以写入追加的方式打开
        $this->fp = fopen('php://output', 'a');

        //检查标题配置信息是否完整
        empty($header_arr) && $header_arr = [];
        $this->checkTitleConfig($header_arr);
        //加载标题信息
        $this->loadTitleInfo($header_arr);
        $this->header_arr        = $header_arr;
        $max_export_number       = (int)$max_export_number;
        $this->max_export_number = empty($max_export_number) ? 0 : $max_export_number;
    }

    /**
     * 加载汇总信息
     * @param array $total_info 汇总信息
     * @return static
     */
    public function loadTotalInfo(array $total_info)
    {
        if (empty($total_info)) {
            return $this;
        }
        $output_data = [];
        foreach ($this->header_arr as $column_key => $title) {
            $output_data[] = empty($total_info[$column_key]) ? '' : iconv('UTF-8', 'GBK//IGNORE', $total_info[$column_key]);
        }
        fputcsv($this->fp, $output_data);
        ob_flush();
        flush();

        return $this;
    }

    /**
     * 用途： 载入一批数据
     * @param array $list 一批数据
     * @return static
     */
    public function loadData(array $list)
    {
        if (empty($list)) {
            return $this;
        }

        foreach ($list as $index => $row) {
            $output_data = [];
            foreach ($this->header_arr as $column_key => $title) {
                $output_data[] = empty($row[$column_key]) ? '' : iconv('UTF-8', 'GBK//IGNORE', $row[$column_key]);
            }
            fputcsv($this->fp, $output_data);
            ob_flush();
            flush();
        }

        return $this;
    }

    /**
     * 最后执行的下载方法
     * @param string $file_base_name 下载的文件主名-不含后缀
     * @return void
     */
    public function download($file_base_name)
    {
        ob_end_clean();
        exit('');
    }

    /**
     * 检查标题配置信息是否完整
     * @param array $header_arr 标题配置信息
     * @return void
     */
    protected function checkTitleConfig($header_arr)
    {
        if (empty($header_arr)) {
            $msg         = iconv('UTF-8', 'GBK//IGNORE', '标题配置信息为空');
            $output_info = [$msg];
            fputcsv($this->fp, $output_info);
            //每批数据就刷新一次缓冲区,一批含有100条spu及其内部包含的所有sku
            ob_flush();
            flush();
            ob_end_clean();
            exit('');
        }
    }

    /**
     * 输出csv标题栏信息
     * @param array $header_arr 标题配置信息
     * @return void
     */
    protected function loadTitleInfo(array $header_arr)
    {
        $title_arr = array_values($header_arr);
        //中文转换
        foreach ($title_arr as $index => $title) {
            $title_arr[$index] = iconv('UTF-8', 'GBK//IGNORE', $title);
        }
        //输出单元格首行标题
        fputcsv($this->fp, $title_arr);
        ob_flush();
        flush();
    }

}
