<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use QL\QueryList;

class QueryListController extends Controller
{
    //
    public function range()
    {
        $html=<<<STR
            <div class="content">
                <div>
                    <a href="https://querylist.cc/1.html">这是链接一</a>
                </div>
                <div>
                    <a href="https://querylist.cc/2.html">这是链接二</a>
                    <span>这是文字二</span>
                </div>
                <div>
                    <a href="https://querylist.cc/1.html">这是链接三</a>
                    <span>这是文字三</span>
                </div>
            </div>
STR;
//采集规则
        $rules = [
            //采集a标签的href属性
            'link' => ['a','href'],
            //采集a标签的text文本
            'link_text' => ['a','text'],
            //采集span标签的text文本
            'txt' => ['span','text']
        ];
        $range='.content>div';
        $ql = QueryList::html($html)->rules($rules)->range($range)->query();
        $data = $ql->getData();
        dd($data->all());
    }


    public function removeHead()
    {
        $html = file_get_contents('http://www.baidu.com/s?wd=QueryList');
        $ql = QueryList::rules([
            'title'=>array('h3','text'),
            'link'=>array('h3>a','href')
        ]);
        $data = $ql->html($html)->removeHead()->query()->getData();
        dd($data);
    }

    public function query(){
        $ql = QueryList::get('http://www.baidu.com/s?wd=QueryList')->rules([
            'title'=>array('h3','text'),
            'link'=>array('h3>a','href')
        ]);
        $data = $ql->query(function($item){
            $item['title'] = $item['title'].' - other string...';
            return $item;
        })->getData();
        dd($data->all());
    }

    public function getData()
    {
        $html =<<<STR
            <div class="xx">
                <img data-src="/path/to/1.jpg" alt="">
            </div>
            <div class="xx">
                <img data-src="/path/to/2.jpg" alt="">
            </div>
            <div class="xx">
                <img data-src="/path/to/3.jpg" alt="">
            </div>
STR;
        $baseUrl = 'http://xxxx.com';
        $ql = QueryList::html($html)->rules([
            'image' => array('.xx>img','data-src')
        ])->query();
        $data=$ql->getData(function($item) use($baseUrl){
//            return $item;       //无处理返回二维数组
//            return $item['image'];  //返回一维数组
            return $baseUrl.$item['image'];
        });
//        dd($data->all());

        $data2=$ql->getData();      //返回collection对象
        dd($data2->all());
    }

    //采集日本雅虎拍卖商品详情页
    public function getGoodsInfo()
    {
        //商品详情页地址
        $url = 'https://page.auctions.yahoo.co.jp/jp/auction/c633799624';

        //抓取商品轮播图
        $product_image_rules = [
            'product_images' => ['.ProductImage__inner>img','src'],
        ];
        $html = QueryList::get($url);
        $ql = $html->rules($product_image_rules);
        $product_images = $ql->query()->getData(function ($item){
            return $item['product_images'];
        });
        $product_images=$product_images->all();

        //抓取商品信息
        $product_detail_rules = [
            'product_detail_titles' => ['.ProductDetail__title','text'],
            'product_detail_descriptions' => ['.ProductDetail__description','text','-span'],
        ];

        $ql = $html->rules($product_detail_rules);
        $product_details = $ql->query()->getData(function ($item){
            return $item;
        });
        $product_details=$product_details->all();


        //抓取商品描述
        $product_explanation_rules = [
            'product_explanation' => ['.ProductExplanation__body','html','a'],
        ];

        $ql = $html->rules($product_explanation_rules);
        $product_explanation = $ql->query()->getData(function ($item){
            return $item['product_explanation'];
        });
        $product_explanation = $product_explanation->all()[0];

        //抓取商品当前出价次数
        $count_number_rules = [
            'count_number'=>['.Count__number:eq(0)','text','-a -.Count__unit']
        ];
        $ql = $html->rules($count_number_rules);
        $product_count_number = $ql->query()->getData(function ($item){
            return $item['count_number'];

        });
        $bid_count = $product_count_number->all()[0];

        echo "<pre>";
        print_r($product_images);
        print_r($product_details);
        print_r($product_explanation);
        print_r($bid_count);

    }

}
