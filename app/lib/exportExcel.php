<?php

namespace app\lib;

use PHPExcel_Cell;
use PHPExcel_Worksheet_MemoryDrawing;

/**
 * 导出excel标准数据操作类(需要composer安装PHPExcel类库)
 * 如果重复往同一个页签($sheet_name)加载数据，则为追加式导出，适用于逐页导出模式。
 * 范例如下：
 * $phpExcel = new exportExcel();
 * $headerArr = [
 * 'range_name' => '销量区间',
 * 'spu_number' => 'SPU数',
 * 'spu_number_proportion' => 'SPU数量占比',
 * 'sale_number' => '总销量',
 * 'sale_amount' => '销售额',
 * 'sale_amount_proportion' => '销售额占比',
 * 'spu_number_1'  => '定制-SPU数',
 * 'sale_number_1' => '定制-销量',
 * 'sale_amount_1' => '定制-销售额',
 * 'spu_number_2'  => '现货-SPU数',
 * 'sale_number_2' => '现货-销量',
 * 'sale_amount_2' => '现货-销售额',
 * 'spu_number_3'  => '成衣-SPU数',
 * 'sale_number_3' => '成衣-销量',
 * 'sale_amount_3' => '成衣-销售额',
 * 'spu_number_4'  => '底版-SPU数',
 * 'sale_number_4' => '底版-销量',
 * 'sale_amount_4' => '底版-销售额',
 * 'spu_img'       => 'SPU图片',
 * 'sku_img'       => 'SKU图片',
 * ];
 * $option = [
 * 'totalInfoPos' => 1,
 * 'floatFieldArr' => ['sale_amount_1', 'sale_amount_2', 'sale_number_3', 'sale_number_4'],
 * 'imgFieldArr' => ['spu_img', 'sku_img'],
 * 'merge_column' => [
 *      'department_name',
 *       'real_name',
 *       ],
 * ];
 * $phpExcel->loadSheetData($headerArr, $affiliatesData['allSaleList'], $affiliatesData['totalAllSaleList'], '动销汇总',
 * $option);
 * $phpExcel->download('测试下载的文件名');
 */
class exportExcel
{
    protected $objPHPExcel      = '';
    protected $is_create_sheet  = 0;
    protected $current_sheet;
    protected $current_row_num  = 0;
    protected $sheet_title_list = [];

    /**
     * 初始化导出环境及对象
     * @param array $config
     */
    public function __construct()
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('default_socket_timeout', 1800);
        require_once  ROOT_PATH.'vendor/PHPExcel/PHPExcel.php';
        $this->objPHPExcel = new \PHPExcel();
    }

    /**
     * 用途： 载入一个页面的数据
     * @param array  $header_arr     表头信息
     * @param array  $data_list      数据
     * @param array  $total_info     汇总数据
     * @param string $sheet_name     excel页签名
     * @param array  $option         $option['totalInfoPos'] - 汇总的位置，0=数据前面，1=数据末尾 ,
     *                               $option['imgFieldArr'] - 导出图片的字段列表
     *                               $option['floatFieldArr'] - 导出的浮点型数据字段列表，字段将自动保留2位小数点
     *                               格式如:
     *                               $option = [
     *                               'totalInfoPos' => 0,
     *                               'imgFieldArr' => ['spu_img','sku_img'],
     *                               'floatFieldArr' => ['sale_amount_1', 'sale_amount_2'],
     *                               ];
     * @return void
     * @throws \PHPExcel_Exception
     */
    public function loadSheetData($header_arr, $data_list = [], $total_info = [], $sheet_name = '', $option = [])
    {
        $is_append = in_array($sheet_name, $this->sheet_title_list);
        $this->setSheet($sheet_name);
        empty($option['imgFieldArr']) && $option['imgFieldArr'] = [];
        empty($option['floatFieldArr']) && $option['floatFieldArr'] = [];
        $is_append || $this->loadHeaderInfo($header_arr, $option);

        // 汇总放在头部
        $is_append || (empty($option['totalInfoPos']) && $this->loadTotalInfo($header_arr, $total_info));

        //加载数据
        foreach ($data_list as $row) {
            $this->current_row_num++;
            $index = 0;
            foreach ($header_arr as $key => $value) {
                $column_value = isset($row[$key]) ? $row[$key] : '';
                $column_key   = PHPExcel_Cell::stringFromColumnIndex($index);
                $current_res  = in_array($key, $option['imgFieldArr']) ? $this->loadImageColumnValue($column_value, $column_key, $this->current_row_num) : false;
                $current_res || $this->current_sheet->setCellValue($column_key . $this->current_row_num, $column_value);
                in_array($key, $option['floatFieldArr']) && $this->current_sheet->getStyle($column_key . $this->current_row_num)->getNumberFormat()->setFormatCode('0.00');
                $index++;
            }
        }

        //如需要合并行，则预先处理数据,影响范围：右侧合并行范围必须在最左侧合并行范围之内，相邻则合并。合并信息及内容临时记录
        empty($option['merge_column']) || $this->mergeCells($header_arr, $data_list, $total_info, $is_append, $option);

        // 汇总放在尾部
        $is_append || (empty($option['totalInfoPos']) || $this->loadTotalInfo($header_arr, $total_info));
    }

    /**
     * 用途： 合并单元格
     * @param array  $header_arr     表头信息
     * @param array  $data_list      数据
     * @param array  $total_info     汇总数据
     * @param boolean $is_append     是否追加的数据
     * @param array  $option         选填配置信息
     * @return void
     */
    private function mergeCells($header_arr, $data_list = [], $total_info = [], $is_append = false, $option = [])
    {
        $merge_info = [
            'value'       => '',
            'column_key'  => '',
            'start_index' => 0,
            'end_index'   => 0,
            'index_list'  => [],
        ];
        //1.先整理限制行
        $limit_column_key = empty($option['merge_limit_column']) ? $option['merge_column'][0] : $option['merge_limit_column'];
        if ($is_append) {
            $data_index = $this->current_row_num + 1;
        } else {
            $data_index = empty($total_info) ? 2 : (empty($option['totalInfoPos']) ? 3 : 2);
        }
        foreach ($data_list as $item_index => $row) {
            $column_value = empty($row[$limit_column_key]) ? '' : $row[$limit_column_key];
            if ($column_value == $merge_info['value']) {
                $merge_info['index_list'][] = $data_index;
                $merge_info['end_index']    = $data_index;
            } else {
                empty($merge_info['value']) || $limit_range_list[] = $merge_info;
                $merge_info = [
                    'value'       => $column_value,
                    'column_key'  => $limit_column_key,
                    'start_index' => $data_index,
                    'end_index'   => $data_index,
                    'index_list'  => [$data_index],
                ];
            }
            $data_index++;
        }
        empty($merge_info['value']) || $limit_range_list[] = $merge_info;

        //计算所有待合并字段合并信息
        $all_merge_list = $limit_range_list;
        foreach ($option['merge_column'] as $merge_index => $merge_column_key) {
            if (empty($merge_column_key)) {
                continue;
            }
            if ($merge_column_key == $limit_column_key) {
                continue;
            }

            if ($is_append) {
                $data_index = $this->current_row_num + 1;
            } else {
                $data_index = empty($total_info) ? 2 : (empty($option['totalInfoPos']) ? 3 : 2);
            }

            //根据限制行计算其他合并行
            foreach($limit_range_list as $limit_info){
                empty($limit_info['index_list']) && $limit_info['index_list'] = [];
                $merge_info = [
                    'value'       => '',
                    'column_key'  => '',
                    'start_index' => 0,
                    'end_index'   => 0,
                    'index_list'  => [],
                ];
                foreach($limit_info['index_list'] as $new_data_index){
                    $item_index = $new_data_index - $data_index;
                    $column_value = empty($data_list[$item_index][$merge_column_key]) ? '' : $data_list[$item_index][$merge_column_key];
                    if ($column_value == $merge_info['value']) {
                        $merge_info['index_list'][] = $new_data_index;
                        $merge_info['end_index']    = $new_data_index;
                    } else {
                        empty($merge_info['value']) || $all_merge_list[] = $merge_info;
                        $merge_info = [
                            'value'       => $column_value,
                            'column_key'  => $merge_column_key,
                            'start_index' => $new_data_index,
                            'end_index'   => $new_data_index,
                            'index_list'  => [$new_data_index],
                        ];
                    }
                    //$data_index++;
                }//end foreach $limit_info['index_list']
                empty($merge_info['value']) || $all_merge_list[] = $merge_info;
            }//end foreach $limit_range_list
        }//end foreach merge_column

        //2.开始循环合并行
        $index = 0;
        $header_key_list = [];
        foreach ($header_arr as $key => $value) {
            $column_key   = PHPExcel_Cell::stringFromColumnIndex($index);
            $header_key_list[$key] = $column_key;
            $index++;
        }
        foreach ($all_merge_list as $merge_info){
            if($merge_info['start_index']==$merge_info['end_index']){
                continue;
            }
            if(empty($header_key_list[$merge_info['column_key']])){
                continue;
            }
            //计算列名
            $column_key = $header_key_list[$merge_info['column_key']];
            $this->current_sheet->mergeCells($column_key.$merge_info['start_index'].':'.$column_key.$merge_info['end_index']);
            $this->current_sheet->setCellValue($column_key . $merge_info['start_index'], $merge_info['value']);
            $this->current_sheet->getStyle($column_key . $merge_info['start_index'])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
        }

        return true;
    }

    /**
     * 设置当前默认sheet页
     * 1.第一个sheet默认进来不用创建
     * 2.持续追加数据不用创建
     * @param string $sheet_name excel页签名字
     * @throws \PHPExcel_Exception
     */
    private function setSheet($sheet_name)
    {
        //已创建的，必定是追加
        if (in_array($sheet_name, $this->sheet_title_list)) {
            $this->current_sheet = $this->objPHPExcel->getActiveSheet();
            return true;
        }
        //非追加方式更新页签
        $this->current_sheet = $this->is_create_sheet ? $this->objPHPExcel->createSheet() : $this->objPHPExcel->getActiveSheet();
        $this->current_sheet->setTitle($sheet_name);
        $this->sheet_title_list[] = $sheet_name;
        $this->is_create_sheet    = 1;
    }

    /**
     * 加载标题信息
     * @param array $header_arr 标题信息数组
     * @param array $option     配置项
     * @return int
     */
    private function loadHeaderInfo($header_arr, $option)
    {
        $index                 = 0;
        $this->current_row_num = 1;
        foreach ($header_arr as $key => $value) {
            $column_key = PHPExcel_Cell::stringFromColumnIndex($index);
            $this->current_sheet->setCellValue($column_key . $this->current_row_num, $value)
                                ->getStyle($column_key . $this->current_row_num)->getFont()->setBold(true);
            in_array($key, $option['imgFieldArr']) && $this->current_sheet->getColumnDimension($column_key)->setWidth(11);
            $index++;
        }

        return $this->current_row_num;
    }

    /**
     * 加载汇总信息
     * @param array $header_arr excel头行信息，用于按字段及顺序输出汇总信息
     * @param array $total_info 汇总信息
     * @return bool
     */
    private function loadTotalInfo($header_arr, $total_info)
    {
        if (empty($total_info)) {
            return false;
        }

        $index = 0;
        $this->current_row_num++;
        foreach ($header_arr as $key => $value) {
            $total_value = isset($total_info[$key]) ? $total_info[$key] : '';
            $this->current_sheet->setCellValue(PHPExcel_Cell::stringFromColumnIndex($index++) . $this->current_row_num, $total_value);
        }
        return true;
    }

    /**
     * 加载图片数据
     * @param string $image_url  图片地址
     * @param string $column_key 加载图片的excel列坐标
     * @param int    $row_num    加载图片的excel行坐标
     * @return bool
     * @throws \PHPExcel_Exception
     */
    private function loadImageColumnValue($image_url, $column_key, $row_num)
    {
        if (empty($image_url)) {
            return false;
        }

        $download_goods_img  = $image_url . '?x-oss-process=image/resize,m_lfit,w_100,quality,q_80';
        $objDrawing          = new \PHPExcel_Worksheet_MemoryDrawing();
        $image_info          = get_headers(trim($download_goods_img), true);
        $image_type          = empty($image_info['Content-Type']) ? '' : strtolower($image_info['Content-Type']);
        $is_other_image_type = false;
        try {
            switch ($image_type) {
                case 'image/webp':
                    $img = imagecreatefromwebp($download_goods_img);
                    break;
                case 'image/jpeg':
                    $img = imagecreatefromjpeg($download_goods_img);
                    break;
                case 'image/gif':
                    $img = imagecreatefromgif($download_goods_img);
                    break;
                case 'image/png':
                    $img = imagecreatefrompng($download_goods_img);
                    break;
                default:
                    $is_other_image_type = true;
                    break;
            }
        } catch (\Exception $e) {
            $is_other_image_type = true;
        }
        if ($is_other_image_type) {
            return false;
        }
        $objDrawing->setImageResource($img);
        $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_DEFAULT);//渲染方法

        $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
        $objDrawing->setWidth(64);
        $objDrawing->setCoordinates($column_key . $row_num);
        $objDrawing->setOffsetX(7);
        $objDrawing->setOffsetY(6);
        $objDrawing->setWorksheet($this->current_sheet);
        $this->current_sheet->getRowDimension($row_num)->setRowHeight(76);

        return true;
    }

    /**
     * 最后执行的下载方法
     * @param string $file_base_name 下载的文件主名-不含后缀
     * @return void
     * @throws \PHPExcel_Writer_Exception
     * @throws \PHPExcel_Exception
     */
    public function download($file_base_name)
    {
        $this->objPHPExcel->setActiveSheetIndex(0);
        $file_base_name = iconv('utf-8', 'gb2312', $file_base_name);
        $file_name      = $file_base_name . '.xls';
        $xlsWriter      = new \PHPExcel_Writer_Excel5($this->objPHPExcel);
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $file_name . '"');
        header("Content-Transfer-Encoding: binary");
        header("Expires: Mon, 26 Jul 2030 05:00:00 GMT");
        header("Last-Modified: " . date("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $xlsWriter->save("php://output");
        exit('');
    }

}