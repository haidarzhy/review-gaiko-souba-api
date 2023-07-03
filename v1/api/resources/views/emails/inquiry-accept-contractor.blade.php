
<!DOCTYPE html>
<html>
<head>
    <title>この度は、お問い合わせいただき誠にありがとうございます</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow">
    <style>
        body,html {
            width: 100%;
            margin: 0;
        }
        body {
            -webkit-text-size-adjust: 100%;
            word-wrap: break-word;
            overflow-wrap: break-word;
            font-variant-ligatures: none;
            font-style: normal;
            font-family: "游ゴシック体", "Yu Gothic", YuGothic, sans-serif;
            background: #F5F4EA;
        }
        * {
            box-sizing: border-box;
        }
        .mail-wrap-inn {
            max-width: 1000px;
            width: 90%;
            background: #fff;
            margin: 0 auto;
            text-align: center;
        }
        .mail-wrap-inn h2 {
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 0;
            color: #7FB932;
        }
        .mail-wrap-inn p {
            font-size: 14px;
            line-height: 1.6;
            color: #000000;
            font-weight: 500;
            letter-spacing: 0.1em;
        }
        .mail-wrap-inn .button {
            margin: 40px 0 0;
        }
        .mail-wrap-inn .button a {
            text-decoration: none;
            max-width: 350px;
            padding: 19px 0;
            display: block;
            width: 100%;
            text-align: center;
            margin: auto;
            line-height: 1;
            border-radius: 10px;
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            background: #FF7533;
            border: 1px solid #FF7533;
        }
        .result {
            margin: 40px 0;
            padding: 40px;
            border-top: 1px solid #E0E0E0;
            border-bottom: 1px solid #E0E0E0;
        }
        table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        table tr {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #000;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.3;
        }
        table tr th {
            background: #000;
            color: #fff;
            text-align: center;
            padding: 12px 0 7px;
            border-right: 1px solid #000;
            width: 14.5%;
        }
        table tr td {
            padding: 12px 0 7px;
            border-right: 1px solid #000;
            width: 14.5%;
            padding-right: 20px;
            padding-left: 20px;
            text-align: right;
        }
        .first {
            width: 62.3%;
        }
        table tr td:first-child {
            padding-left: 20px;
            text-align: left;
        }
        table tr th:nth-child(2),table tr td:nth-child(2) {
            width: 8.9%;
        }
        table tr:last-child,table tr th:last-child,table tr td:last-child {
            border: none;
        }
        .result dl {
            border: 1px solid #000;
            font-size: 24px;
            font-weight: 700;
            max-width: 500px;
            margin: 0 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .result dt {    
            width: 150px;
            background: #000;
            line-height: 1.7;
            color: #fff;
            text-align: center;
            padding: 9px 0;
        }
        .result dd {    
            margin: 0;
            text-align: center;
            width: calc(100% - 160px);
        }
        .mail-wrap-inn .pc {
            display: block;
        }
        .mail-wrap-inn .sp {
            display: none;
        }
        .text-left {
            text-align: left !important;
        } 
        /* for media SP query 768 */
        @media screen and (max-width: 768px) {
            .mail-wrap-inn .pc {
                display: none;
            }
            .mail-wrap-inn .sp {
                display: block;
            }
            .mail-wrap-inn h2 {
                font-size: 22px;
            }
            .mail-wrap-inn .button a {
                font-size: 18px;
            }
            .result {
                margin: 40px 0;
                padding: 40px 0;
            }
            table tr th {
                padding: 10px 0;
                width: 25.4%;
                font-size: 14px;
            }
            table tr td {
                text-align: center!important;
                padding: 12px 0 7px!important;
                width: 25.4%;
            }
            table tr th:first-child,table tr td:first-child {
                width: 37.3%;
            }
            table tr th:nth-child(2),table tr td:nth-child(2) {
                width: 13.6%;
            }
            .result dl {
                font-size: 20px;
                max-width: 100%;
            }
            .result dt {    
                width: 100px;
                padding: 7px 0;
            }
            .result dd {    
                width: calc(100% - 110px);
            }

        }
        
        @media screen and (max-width: 330px) {
            .result dl {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="mail-wrap">
        <div class="mail-wrap-inn">
            <h3>お問い合わせ受付中！</h3>
            <p>これらの情報を含むアンケートに同意しました。 さらにご不明な点がございましたら、お客様までお問い合わせください。</p>
            <p> ご利用いただきありがとうございます。</p>
            <div class="result">
                <dl>
                    <dt>合計</dt>
                    <dd>{{ number_format($inquiry->total, 0, '.', ',') }}円(税込)</dd>
                </dl>
                <table>
                    <tr>
                        <th class="first">摘要</th>
                        <th>数量</th>
                        <th>単価</th>
                        <th>金額</th>
                    </tr>
                    @if($inquiry->inquiryQuotes != null)
                        @foreach($inquiry->inquiryQuotes as $iq)
                            <tr>
                                <td class="first text-left">{{ $iq->quotation->q_name }}</td>
                                <td>{{ $iq->quantity }}</td>
                                <td>{{ number_format($iq->unit_price, 0, '.', ',') }}</td>
                                <td>{{ number_format($iq->amount, 0, '.', ',') }}</td>
                            </tr>
                        @endforeach
                    @endif
                    
                    
                </table>
            </div>
            <div class="button">
                <a href="https://gaiko-souba-net.icdl.tokyo/" rel="noopener noreferrer" target="_blank">ホームへ戻る</a>
            </div>
        </div>
    </div>
</body>
</html>