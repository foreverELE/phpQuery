<?php

namespace App\Http\Controllers;

use App\Models\Category;
use GuzzleHttp\Client;
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
                    <span>这是文字二.二</span>
                    <span>这是文字二.二.二</span>
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
        $ql = QueryList::html($html)->rules($rules)->range($range)->query(function($item){
            return $item;
        });
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
        $url = "https://page.auctions.yahoo.co.jp/jp/auction/k286914840";

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

    //获取拍卖商品列表页的商品URL（出价次数排序）
    public function getGoodsLists()
    {
        $goods_lists_url='https://auctions.yahoo.co.jp/category/list/%E3%82%A2%E3%83%B3%E3%83%86%E3%82%A3%E3%83%BC%E3%82%AF-%E3%82%B3%E3%83%AC%E3%82%AF%E3%82%B7%E3%83%A7%E3%83%B3-%E3%82%AA%E3%83%BC%E3%82%AF%E3%82%B7%E3%83%A7%E3%83%B3/20000/?p=%E3%82%A2%E3%83%B3%E3%83%86%E3%82%A3%E3%83%BC%E3%82%AF%E3%80%81%E3%82%B3%E3%83%AC%E3%82%AF%E3%82%B7%E3%83%A7%E3%83%B3&tab_ex=commerce&fr=auc-prop&select=03&b=1';
        $range='.a1wrp>h3';
        $goods_lists_rules=[
            'href'=>['a','href'],
            'title'=>['a','text'],
        ];

        $html=QueryList::get($goods_lists_url);
        $ql=$html->rules($goods_lists_rules)->range($range)->query()->getData(function($item){
            return $item;
        });
        dd($ql->all());


    }

    //登录form
    public function getLoginForm()
    {
        $login_url='https://login.yahoo.co.jp/config/login';

        //
        $login_rules = [
            'form' => ['#loginfs','html','-#links -legend'],
        ];
        $html=QueryList::get($login_url);
        $ql=$html->rules($login_rules)->query()->getData(function($item){
            return $item['form'];
        })->all()[0];

        echo $ql;
    }


    public function Test()
    {
        $url="https://api.accounts.yahoo.co.jp/api/getUserAttr?key={%22data_code%22:[%22pr%22,%22sb%22,%22dnm%22,%22dnmicn%22,%22bpr%22,%22ym%22]}&date=1514891268240";

        echo urldecode($url);
    }

    public function apiGet()
    {

//        return view('api_get');
        $str='ftYaCXoLr0IcsMrxcCn6c41sUurf9oNmXJiADMMmZA_dVtRmqH7m2F17';
        $str='tXR0v3oLr0IgCMS0uWvpk_qFH5Gr_JSi2scPm1xR0tpA5IFdq92Z.EGr';
        dd(base64_decode($str));
//        date_default_timezone_set('Asia/Shanghai');
        $url=urldecode("https://api.accounts.yahoo.co.jp:443/api/getUserAttr?key={%22data_code%22:[%22pr%22,%22sb%22,%22dnm%22,%22dnmicn%22,%22bpr%22,%22ym%22]}&date=");
        $timestamp_arr=explode(' ',microtime());      //url后的date参数为秒记时间戳+三位毫秒数
        $timestamp=$timestamp_arr[1];
        $micro_sec=substr($timestamp_arr[0],2,3);
        $date=$timestamp.$micro_sec;
        $url=$url.$date;

        echo $url,'<br />';

        $client = new Client();
        $res = $client->request('GET', $url, [
            'headers' => [
                'Accept-Encoding' => 'gzip, deflate, br',
                'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8',
                'Content-Type'    => 'text/plain',
                'Cookie'          => 'sAuc=d=e4GkWVtRm5veFj9rFtPdVxSyIPUoT2APIrAhXseXoorFQj5V_w3ikASDknATAPAzecNOCVCzXqz9dLrQthI2fO8IjlvscfQOuJoZwTJf_dI9Kw--&v=1; irepNoBidExp=1; irepLastBidTime=2; F=a=0uAsoxEMvSJIHMFCY1kI.tisVXnM06wXGZ1XlWGq_uxuAFVX9KyIEYSSVzzzKcomDqHCFyYgoEKhYK1iV2eXzmYv3A--&b=7cox; B=34k3e85d2i73f&b=4&d=fjtQvoppYF3nR1gkxdL9.zH2XvpvPuA6ezvaD7ZT&s=c8&i=T8Iau.FfVe7Q8u1icEgv; Y=v=1&n=av5lg4ofesacj&l=7kc0d_srrruqvyvy/o&p=m2svvjp012000000&r=131&lg=ja-JP&intl=jp; T=z=AtzSaBAVCcaB/FsXyDvsOLJNjc2NAYwN04xTk5PMjU-&sk=DAAVNcinzN109I&ks=EAAUcExOdqfLO2iGIK9n1I1XA--~F&kt=EAA2hJWwkWGN.MZQV.orgbznA--~E&ku=FAAMEQCIDzuWMF3me5oeyEPwcu4QVKIxv2yTpNqOqKtwVcVlhZGAiA8WliUv66i7VhMyfpS1b4jYn0w3nZNvufYkDDFqo6Kzg--~A&d=dGlwAUd2Z3MwQQFhAVlBRQFnAU9aS05HTVlJVFQ2WE1JN1hVUDVXR05ESTRFAXNsAU1UQXhNd0UzTURrMk9UazROVEktAWFsATIxMTE0MDU4NThAcXEuY29tAXNjAWF1YwF6egFBdHpTYUJBMko-; SSL=v=1&s=a4UatTVTM.SYPDNHAax6RQkpG8yT9Ek6jcoIL7m1roiNaGdpRgsDwB3zYbccNDGYR0BaH5vUffgNUWp_PfUU1Q--&kv=0; _n=eyJhbGciOiJSUzI1NiIsImtpZCI6IjEiLCJ0eXAiOiJKV1QifQ.eyJzdWIiOiJodW1hbl8yMTExNDA1ODU4IiwiZ3VpZCI6Ik9aS05HTVlJVFQ2WE1JN1hVUDVXR05ESTRFIiwiaXNzIjoiaHR0cHM6XC9cL2xvZ2luLnlhaG9vLmNvLmpwIiwidGgiOiJET2ZheEo4X3VlQXZnQklxOHJSSDRnIiwiaWF0IjoxNTE0ODc5ODA4LCJleHAiOjE1MTcyOTkwMDgsImp0aSI6IjA2MDJlYmE4LTYwM2YtNGJkNS04YTdjLTgzOGYxYjY0OWRjNCIsImxjeCI6eyJhYXQiOjE1MTQ4Nzk4MDgsImFtciI6WyJwd2QiXX0sInZjeCI6eyJhYXQiOjE1MTQ4Nzk4MDgsImFtciI6WyJwd2QiXX0sImhpc3QiOlsicHdkIl19.k1dwJs_ZIAHmkYUTYOVR3hFnF3nxYltWyjWWDNHipJ70UdvBBqX8NdYdd6wB63_9fSwnq4MHq2B1tLu_Lkefn6ACsCGuL_RWAxRO0kekVE-8qDvM49i8l_OrLP-P48abt9UuG3zGf2bTskLLU1lb0fJ4LSkHfyQFfo80Iu66PFwn2SbAnIF5TnMm22aU10nu1_ufMpxk8W07Hykne61ARBUkLQc-hAqGuZo6e9I1DUax-cVaPxU0TWzxBvf4bsUWn82oV8v3PRwqxuzM4vfC8PbWbaJhXX4tGj3ekvsV3nzRoeLBvjqcopICu-k4PpxFsLtn0NFpsCiGhvsH3xwm5Q; RD=Ox9.xQD25tctf2315Hu5BXhmwOSj7Fp28xJy24.I.ukbdcoWNipyQlRCUS6a.E.zuQ--', // only allow https URLs
                'Origin'          => 'https://auctions.yahoo.co.jp',
                'Referer'         => 'https://auctions.yahoo.co.jp/user/jp/show/mystatus',
                'User-Agent'      => 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Mobile Safari/537.36X-DevTools-Emulate-Network-Conditions-Client-Id: (334661E1648A2A4B5A56E0A536FED80A)'
            ]
        ]);
        echo $res->getStatusCode();
//        dd(\GuzzleHttp\json_decode($res->getBody())->Code);
// "200"
//        echo $res->getHeader('content-type');
//// 'application/json; charset=utf8'
//        echo $res->getBody();


    }


    //出价跳转,获取name=a 的隐藏域
    public function getBid()
    {
        $ql=QueryList::post('https://auctions.yahoo.co.jp/jp/show/bid_preview',[
            'ItemID' => 'q189348897',
            'login' => 'human_2111405858',
            'cc' => 'jp',
            'bidType' => '1000',
            'lastquantity' => '1',
            'setPrice' => '21',
            'Quantity' => '1',
            'LastBid' => '1',
            'Bid' => '21',
        ],[
            'headers' => [
                'Cookie' => 'YLS=v=2&p=1&n=1; sAuc=d=e4GkWVtRm5veFj9rFtPdVxSyIPUoT2APIrAhXseXoorFQj5V_w3ikASDknATAPAzecNOCVCzXqz9dLrQthI2fO8IjlvscfQOuJoZwTJf_dI9Kw--&v=1; F=a=0uAsoxEMvSJIHMFCY1kI.tisVXnM06wXGZ1XlWGq_uxuAFVX9KyIEYSSVzzzKcomDqHCFyYgoEKhYK1iV2eXzmYv3A--&b=7cox; B=34k3e85d2i73f&b=4&d=fjtQvoppYF3nR1gkxdL9.zH2XvpvPuA6ezvaD7ZT&s=c8&i=T8Iau.FfVe7Q8u1icEgv; Y=v=1&n=av5lg4ofesacj&l=7kc0d_srrruqvyvy/o&p=m2svvjp012000000&r=131&lg=ja-JP&intl=jp; T=z=AtzSaBAVCcaB/FsXyDvsOLJNjc2NAYwN04xTk5PMjU-&sk=DAAVNcinzN109I&ks=EAAUcExOdqfLO2iGIK9n1I1XA--~F&kt=EAA2hJWwkWGN.MZQV.orgbznA--~E&ku=FAAMEQCIDzuWMF3me5oeyEPwcu4QVKIxv2yTpNqOqKtwVcVlhZGAiA8WliUv66i7VhMyfpS1b4jYn0w3nZNvufYkDDFqo6Kzg--~A&d=dGlwAUd2Z3MwQQFhAVlBRQFnAU9aS05HTVlJVFQ2WE1JN1hVUDVXR05ESTRFAXNsAU1UQXhNd0UzTURrMk9UazROVEktAWFsATIxMTE0MDU4NThAcXEuY29tAXNjAWF1YwF6egFBdHpTYUJBMko-; SSL=v=1&s=a4UatTVTM.SYPDNHAax6RQkpG8yT9Ek6jcoIL7m1roiNaGdpRgsDwB3zYbccNDGYR0BaH5vUffgNUWp_PfUU1Q--&kv=0; _n=eyJhbGciOiJSUzI1NiIsImtpZCI6IjEiLCJ0eXAiOiJKV1QifQ.eyJzdWIiOiJodW1hbl8yMTExNDA1ODU4IiwiZ3VpZCI6Ik9aS05HTVlJVFQ2WE1JN1hVUDVXR05ESTRFIiwiaXNzIjoiaHR0cHM6XC9cL2xvZ2luLnlhaG9vLmNvLmpwIiwidGgiOiJET2ZheEo4X3VlQXZnQklxOHJSSDRnIiwiaWF0IjoxNTE0ODc5ODA4LCJleHAiOjE1MTcyOTkwMDgsImp0aSI6IjA2MDJlYmE4LTYwM2YtNGJkNS04YTdjLTgzOGYxYjY0OWRjNCIsImxjeCI6eyJhYXQiOjE1MTQ4Nzk4MDgsImFtciI6WyJwd2QiXX0sInZjeCI6eyJhYXQiOjE1MTQ4Nzk4MDgsImFtciI6WyJwd2QiXX0sImhpc3QiOlsicHdkIl19.k1dwJs_ZIAHmkYUTYOVR3hFnF3nxYltWyjWWDNHipJ70UdvBBqX8NdYdd6wB63_9fSwnq4MHq2B1tLu_Lkefn6ACsCGuL_RWAxRO0kekVE-8qDvM49i8l_OrLP-P48abt9UuG3zGf2bTskLLU1lb0fJ4LSkHfyQFfo80Iu66PFwn2SbAnIF5TnMm22aU10nu1_ufMpxk8W07Hykne61ARBUkLQc-hAqGuZo6e9I1DUax-cVaPxU0TWzxBvf4bsUWn82oV8v3PRwqxuzM4vfC8PbWbaJhXX4tGj3ekvsV3nzRoeLBvjqcopICu-k4PpxFsLtn0NFpsCiGhvsH3xwm5Q; irepNoBidExp=0; irepLastBidTime=0; btpdb.GBd7Bvs.YXVjdGlvbiBpZCBiaWQ=ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5MTY3YTIzMjc5MmFkYjIyM2ViZDQ1NzRiMGMzYWU3ZDg0NjFjODljZTI1NmMwZjAxN2Q1ZmE1NTFiNGQ5MzQ1ZjQ2MWM4OWNlMjU2YzBmMDE3ZDVmYTU1MWI0ZDkzNDVmZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5NDYxYzg5Y2UyNTZjMGYwMTdkNWZhNTUxYjRkOTM0NWY0NjFjODljZTI1NmMwZjAxN2Q1ZmE1NTFiNGQ5MzQ1ZjQ2MWM4OWNlMjU2YzBmMDE3ZDVmYTU1MWI0ZDkzNDVmNDYxYzg5Y2UyNTZjMGYwMTdkNWZhNTUxYjRkOTM0NWY0NjFjODljZTI1NmMwZjAxN2Q1ZmE1NTFiNGQ5MzQ1ZjQ2MWM4OWNlMjU2YzBmMDE3ZDVmYTU1MWI0ZDkzNDVmNDYxYzg5Y2UyNTZjMGYwMTdkNWZhNTUxYjRkOTM0NWY0NjFjODljZTI1NmMwZjAxN2Q1ZmE1NTFiNGQ5MzQ1ZjQ2MWM4OWNlMjU2YzBmMDE3ZDVmYTU1MWI0ZDkzNDVm; RD=vuePJwD25tcrVosM38SbCDJAaXP1By8lGZM5uPDC2Z5SEG4nNRh.ycaybSGEqNeDFGOHrlKgxt8yGrhqnQ_wEpeTPaKnWvqLaO5V_gkIzyF6ImEmxFOV6fIMBGZwwZU-; criteo_seg=75',
            ]
        ]);

        //隐藏域name=a的值
        $a=$ql->find(":hidden[name='a']")->val();
        dd($a);

        $Bid=2001;
        $ItemID='p587419867';
        $url="https://auctions.yahoo.co.jp/jp/config/placebid?Quantity=1&ItemID=p587419867&a=D0fBx3oLr0KtzOemRH8JF5uk6lc0QO5TsFU59HbU9nZQVEfwgqoiasgG&u=&previousBid=";

        $ql=QueryList::post('https://auctions.yahoo.co.jp/jp/show/bid_preview',[
            'ItemID' => 'p587419867',
            'login' => 'human_2111405858',
            'cc' => 'jp',
            'bidType' => '1000',
            'lastquantity' => '1',
            'setPrice' => '1701',
            'Quantity' => '1',
            'LastBid' => '1',
            'Bid' => '1701',
        ],[
            'headers' => [
                'Cookie' => 'YLS=v=2&p=1&n=1; sAuc=d=e4GkWVtRm5veFj9rFtPdVxSyIPUoT2APIrAhXseXoorFQj5V_w3ikASDknATAPAzecNOCVCzXqz9dLrQthI2fO8IjlvscfQOuJoZwTJf_dI9Kw--&v=1; F=a=0uAsoxEMvSJIHMFCY1kI.tisVXnM06wXGZ1XlWGq_uxuAFVX9KyIEYSSVzzzKcomDqHCFyYgoEKhYK1iV2eXzmYv3A--&b=7cox; B=34k3e85d2i73f&b=4&d=fjtQvoppYF3nR1gkxdL9.zH2XvpvPuA6ezvaD7ZT&s=c8&i=T8Iau.FfVe7Q8u1icEgv; Y=v=1&n=av5lg4ofesacj&l=7kc0d_srrruqvyvy/o&p=m2svvjp012000000&r=131&lg=ja-JP&intl=jp; T=z=AtzSaBAVCcaB/FsXyDvsOLJNjc2NAYwN04xTk5PMjU-&sk=DAAVNcinzN109I&ks=EAAUcExOdqfLO2iGIK9n1I1XA--~F&kt=EAA2hJWwkWGN.MZQV.orgbznA--~E&ku=FAAMEQCIDzuWMF3me5oeyEPwcu4QVKIxv2yTpNqOqKtwVcVlhZGAiA8WliUv66i7VhMyfpS1b4jYn0w3nZNvufYkDDFqo6Kzg--~A&d=dGlwAUd2Z3MwQQFhAVlBRQFnAU9aS05HTVlJVFQ2WE1JN1hVUDVXR05ESTRFAXNsAU1UQXhNd0UzTURrMk9UazROVEktAWFsATIxMTE0MDU4NThAcXEuY29tAXNjAWF1YwF6egFBdHpTYUJBMko-; SSL=v=1&s=a4UatTVTM.SYPDNHAax6RQkpG8yT9Ek6jcoIL7m1roiNaGdpRgsDwB3zYbccNDGYR0BaH5vUffgNUWp_PfUU1Q--&kv=0; _n=eyJhbGciOiJSUzI1NiIsImtpZCI6IjEiLCJ0eXAiOiJKV1QifQ.eyJzdWIiOiJodW1hbl8yMTExNDA1ODU4IiwiZ3VpZCI6Ik9aS05HTVlJVFQ2WE1JN1hVUDVXR05ESTRFIiwiaXNzIjoiaHR0cHM6XC9cL2xvZ2luLnlhaG9vLmNvLmpwIiwidGgiOiJET2ZheEo4X3VlQXZnQklxOHJSSDRnIiwiaWF0IjoxNTE0ODc5ODA4LCJleHAiOjE1MTcyOTkwMDgsImp0aSI6IjA2MDJlYmE4LTYwM2YtNGJkNS04YTdjLTgzOGYxYjY0OWRjNCIsImxjeCI6eyJhYXQiOjE1MTQ4Nzk4MDgsImFtciI6WyJwd2QiXX0sInZjeCI6eyJhYXQiOjE1MTQ4Nzk4MDgsImFtciI6WyJwd2QiXX0sImhpc3QiOlsicHdkIl19.k1dwJs_ZIAHmkYUTYOVR3hFnF3nxYltWyjWWDNHipJ70UdvBBqX8NdYdd6wB63_9fSwnq4MHq2B1tLu_Lkefn6ACsCGuL_RWAxRO0kekVE-8qDvM49i8l_OrLP-P48abt9UuG3zGf2bTskLLU1lb0fJ4LSkHfyQFfo80Iu66PFwn2SbAnIF5TnMm22aU10nu1_ufMpxk8W07Hykne61ARBUkLQc-hAqGuZo6e9I1DUax-cVaPxU0TWzxBvf4bsUWn82oV8v3PRwqxuzM4vfC8PbWbaJhXX4tGj3ekvsV3nzRoeLBvjqcopICu-k4PpxFsLtn0NFpsCiGhvsH3xwm5Q; irepNoBidExp=0; irepLastBidTime=0; btpdb.GBd7Bvs.YXVjdGlvbiBpZCBiaWQ=ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5MTY3YTIzMjc5MmFkYjIyM2ViZDQ1NzRiMGMzYWU3ZDg0NjFjODljZTI1NmMwZjAxN2Q1ZmE1NTFiNGQ5MzQ1ZjQ2MWM4OWNlMjU2YzBmMDE3ZDVmYTU1MWI0ZDkzNDVmZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5ZGI1N2RkOWE1NDIyODA1ODM1MGI0OWFmYzYzODk0NzlkYjU3ZGQ5YTU0MjI4MDU4MzUwYjQ5YWZjNjM4OTQ3OWRiNTdkZDlhNTQyMjgwNTgzNTBiNDlhZmM2Mzg5NDc5NDYxYzg5Y2UyNTZjMGYwMTdkNWZhNTUxYjRkOTM0NWY0NjFjODljZTI1NmMwZjAxN2Q1ZmE1NTFiNGQ5MzQ1ZjQ2MWM4OWNlMjU2YzBmMDE3ZDVmYTU1MWI0ZDkzNDVmNDYxYzg5Y2UyNTZjMGYwMTdkNWZhNTUxYjRkOTM0NWY0NjFjODljZTI1NmMwZjAxN2Q1ZmE1NTFiNGQ5MzQ1ZjQ2MWM4OWNlMjU2YzBmMDE3ZDVmYTU1MWI0ZDkzNDVmNDYxYzg5Y2UyNTZjMGYwMTdkNWZhNTUxYjRkOTM0NWY0NjFjODljZTI1NmMwZjAxN2Q1ZmE1NTFiNGQ5MzQ1ZjQ2MWM4OWNlMjU2YzBmMDE3ZDVmYTU1MWI0ZDkzNDVm; RD=vuePJwD25tcrVosM38SbCDJAaXP1By8lGZM5uPDC2Z5SEG4nNRh.ycaybSGEqNeDFGOHrlKgxt8yGrhqnQ_wEpeTPaKnWvqLaO5V_gkIzyF6ImEmxFOV6fIMBGZwwZU-; criteo_seg=75',
            ]
        ]);

        //出价请求
//        $url='https://auctions.yahoo.co.jp/jp/config/placebid?Bid=321&Quantity=1&ItemID=p587419867&a=1NqmUnoLr0L9gUTWEXt3KdYRCIWRhTsYqpBINzE8Z4_sUZwgiCPZ8rww&u=&previousBid='
//        $client = new Client();
//        $res = $client->request('GET', $url, [
//            'headers' => [
//                'Accept-Encoding' => 'gzip, deflate, br',
//                'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8',
//                'Content-Type'    => 'text/plain',
//                'Cookie'          => 'sAuc=d=e4GkWVtRm5veFj9rFtPdVxSyIPUoT2APIrAhXseXoorFQj5V_w3ikASDknATAPAzecNOCVCzXqz9dLrQthI2fO8IjlvscfQOuJoZwTJf_dI9Kw--&v=1; irepNoBidExp=1; irepLastBidTime=2; F=a=0uAsoxEMvSJIHMFCY1kI.tisVXnM06wXGZ1XlWGq_uxuAFVX9KyIEYSSVzzzKcomDqHCFyYgoEKhYK1iV2eXzmYv3A--&b=7cox; B=34k3e85d2i73f&b=4&d=fjtQvoppYF3nR1gkxdL9.zH2XvpvPuA6ezvaD7ZT&s=c8&i=T8Iau.FfVe7Q8u1icEgv; Y=v=1&n=av5lg4ofesacj&l=7kc0d_srrruqvyvy/o&p=m2svvjp012000000&r=131&lg=ja-JP&intl=jp; T=z=AtzSaBAVCcaB/FsXyDvsOLJNjc2NAYwN04xTk5PMjU-&sk=DAAVNcinzN109I&ks=EAAUcExOdqfLO2iGIK9n1I1XA--~F&kt=EAA2hJWwkWGN.MZQV.orgbznA--~E&ku=FAAMEQCIDzuWMF3me5oeyEPwcu4QVKIxv2yTpNqOqKtwVcVlhZGAiA8WliUv66i7VhMyfpS1b4jYn0w3nZNvufYkDDFqo6Kzg--~A&d=dGlwAUd2Z3MwQQFhAVlBRQFnAU9aS05HTVlJVFQ2WE1JN1hVUDVXR05ESTRFAXNsAU1UQXhNd0UzTURrMk9UazROVEktAWFsATIxMTE0MDU4NThAcXEuY29tAXNjAWF1YwF6egFBdHpTYUJBMko-; SSL=v=1&s=a4UatTVTM.SYPDNHAax6RQkpG8yT9Ek6jcoIL7m1roiNaGdpRgsDwB3zYbccNDGYR0BaH5vUffgNUWp_PfUU1Q--&kv=0; _n=eyJhbGciOiJSUzI1NiIsImtpZCI6IjEiLCJ0eXAiOiJKV1QifQ.eyJzdWIiOiJodW1hbl8yMTExNDA1ODU4IiwiZ3VpZCI6Ik9aS05HTVlJVFQ2WE1JN1hVUDVXR05ESTRFIiwiaXNzIjoiaHR0cHM6XC9cL2xvZ2luLnlhaG9vLmNvLmpwIiwidGgiOiJET2ZheEo4X3VlQXZnQklxOHJSSDRnIiwiaWF0IjoxNTE0ODc5ODA4LCJleHAiOjE1MTcyOTkwMDgsImp0aSI6IjA2MDJlYmE4LTYwM2YtNGJkNS04YTdjLTgzOGYxYjY0OWRjNCIsImxjeCI6eyJhYXQiOjE1MTQ4Nzk4MDgsImFtciI6WyJwd2QiXX0sInZjeCI6eyJhYXQiOjE1MTQ4Nzk4MDgsImFtciI6WyJwd2QiXX0sImhpc3QiOlsicHdkIl19.k1dwJs_ZIAHmkYUTYOVR3hFnF3nxYltWyjWWDNHipJ70UdvBBqX8NdYdd6wB63_9fSwnq4MHq2B1tLu_Lkefn6ACsCGuL_RWAxRO0kekVE-8qDvM49i8l_OrLP-P48abt9UuG3zGf2bTskLLU1lb0fJ4LSkHfyQFfo80Iu66PFwn2SbAnIF5TnMm22aU10nu1_ufMpxk8W07Hykne61ARBUkLQc-hAqGuZo6e9I1DUax-cVaPxU0TWzxBvf4bsUWn82oV8v3PRwqxuzM4vfC8PbWbaJhXX4tGj3ekvsV3nzRoeLBvjqcopICu-k4PpxFsLtn0NFpsCiGhvsH3xwm5Q; RD=Ox9.xQD25tctf2315Hu5BXhmwOSj7Fp28xJy24.I.ukbdcoWNipyQlRCUS6a.E.zuQ--', // only allow https URLs
//                'Origin'          => 'https://auctions.yahoo.co.jp',
//                'Referer'         => 'https://auctions.yahoo.co.jp/user/jp/show/mystatus',
//                'User-Agent'      => 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Mobile Safari/537.36X-DevTools-Emulate-Network-Conditions-Client-Id: (334661E1648A2A4B5A56E0A536FED80A)'
//            ]
//        ]);
//        echo $res->getStatusCode();

    }

    //获取拍卖分类
    public function getAuctionCategory()
    {
        $url="https://auctions.yahoo.co.jp/list1/jp/0-all.html#23336";
        $range='.acMdSiteMapList > div > div > dl';
//        $rules = [
//            //采集一级分类a标签的text文本
//            'first_txt' => ['.decSiteMapListTl > h2 > a','text'],
//            //采集二级分类a标签text的文本
//            'sec_txt' => ['dd:not(".decSiteMapListTl") > a','text'],
//            //采集二级分类a标签的href属性
//            'sec_href' => ['dd:not(".decSiteMapListTl") a','href']
//        ];
//
//        $ql = QueryList::get($url)->range($range)->rules($rules)->query(function($item){
//            return $item;
//        });
//        $data = $ql->getData()->all();

        //一级分类标题
        $html=QueryList::get($url);
        $first_title=$html->find('.decSiteMapListTl > h2')
            ->texts()
            ->mapWithKeys(function($item){
                static $i=0;
                $i++;

                return [$item => $i];
            });
//        dd($first_title->all());
        //所有二级分类标题
        $sec_title=$html->find('.acMdSiteMapList > div > div > dl > dd >a')
            ->texts();
        //所有二级分类对应的链接
        $sec_href=$html->range($range)->find('dd >a')
            ->attrs('href');
        dd($sec_href);
    }

    //api获取分类测试
    public function getCategory($category_id = 0)
    {
        $url='https://auctions.yahooapis.jp/AuctionWebService/V2/categoryTree?appid=dj00aiZpPVo4WUpTVHdnWmN0eSZzPWNvbnN1bWVyc2VjcmV0Jng9MDQ-&category='.$category_id;
        $xml=simplexml_load_file($url);
        $arr=json_decode(\GuzzleHttp\json_encode($xml));
        return $arr->Result;
    }

    public function showCategory($category_id = 0,$lev = 0)
    {
        $res=$this->getCategory($category_id);
        $parent_category_id=isset($res->ParentCategoryId)?$res->ParentCategoryId:'';
        $yh_category_id=$res->CategoryId;
        $category_name_jp=$res->CategoryName;
        $child_category_num=isset($res->ChildCategoryNum)?$res->ChildCategoryNum:0;

        $arr=array();
        $arr[]=array(
            'lev' => $lev,
            'yh_category_id' => $yh_category_id,
            'child_category_num' => $child_category_num,
            'name_jp' => $category_name_jp,
            'yh_parent_category_id' => $parent_category_id,
        );
//        print_r($arr);

        if($child_category_num>0 && $lev <= 0){
            foreach($res->ChildCategory as $child_category){
                $category_id=$child_category->CategoryId;
                $data=$this->showCategory($category_id, $lev+1);
                $arr=!empty($data)?array_merge($arr,$data):$arr;
            }
        }

        return $arr;
    }

    public function index()
    {
//        echo "<pre>";
        $data=$this->showCategory(0);
        dd($data);
//        foreach($data as $v){
//
//        }



    }

}
