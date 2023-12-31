<!DOCTYPE html>
<html>
<head>
    <title>おめでとう！ 登録が確認されました！</title>
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
        .mail-wrap {
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
        .mail-wrap-inn h3 {
            font-weight: 700;
            font-size: 24px;
        }
        .mail-wrap-inn p {
            font-size: 14px;
            line-height: 1.6;
            color: #000000;
            font-weight: 500;
            letter-spacing: 0.1em;
        }
        .mail-wrap-inn .button {
            margin: 40px 0;
            padding-bottom: 40px;
            border-bottom: 1px solid #E0E0E0;
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
        .contact-address {
            margin-top: 40px;
        }
        .mail-wrap-inn .pc {
            display: block;
        }
        .mail-wrap-inn .sp {
            display: none;
        }
        .align-left {
            text-align: left;
        }
        .password {
            text-align: center;
            margin: 30px 0px;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
        /* for media SP query 768 */
        @media screen and (max-width: 768px) {
            .mail-wrap-inn .pc {
                display: none;
            }
            .mail-wrap-inn .sp {
                display: block;
            }
            .mail-wrap-inn {
                padding: 50px 20px;
            }
            .mail-wrap-inn h2 {
                font-size: 22px;
            }
            .mail-wrap-inn h3 {
                font-size: 18px;
            }
            .mail-wrap-inn .button a {
                font-size: 18px;
            }

        }
    </style>
</head>
<body>
    <div class="mail-wrap">
        <div class="mail-wrap-inn">
            <h2>こんにちは、{{ $name }}様！</h2>
            <h3>おめでとうございます！ <br class="sp">登録が確認されました！</h3>
            <p>
                外構相場.comへの登録が完了しました。<br>
                これで、ログイン画面にアクセスして<br class="sp">注文のリストを確認できる<br class="sp">ようになりました。<br>
                登録したメールアドレスとパスワードを<br class="sp">使用してログイン画面に<br class="sp">アクセスしてください。
            </p>
            <p class="password">{{ $password }}</p>
            <div class="button">
                <a href="https://gaiko-souba-net.icdl.tokyo/sign-in" rel="noopener noreferrer" target="_blank">ログイン画面にアクセスする</a>
            </div>
            <h3>これがあなたの加入プランです。</h3>
            <p>タイプ - {{ $plan }}</p>
            <p>お支払い - {{ $price }}</p>
            <p class="align-left">ご質問やご不明な点がございましたら、info@gaiko-souba.net または 050-3825-7567 まで<br>お気軽にお問い合わせください。</p>
        
            <div class="contact-address align-left">
                <p>
                    <br>外構相場.com
                    <br>info@gaiko-souba.net
                    <br>050-3825-7567
                    <br>〒491-0827 
                    <br>愛知県 一宮市
                    <br>三ツ井4丁目16番15号
                </p>
            </div>
        </div>
    </div>
</body>
</html>