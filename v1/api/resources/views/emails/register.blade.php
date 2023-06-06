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
            padding: 100px 0;
        }
        .mail-wrap-inn {
            max-width: 1000px;
            width: 90%;
            background: #fff;
            margin: 0 auto;
            padding: 60px 50px;
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
            <h2>こんにちは、{{ $name }}！</h2>
            <h3>おめでとう！ <br class="sp">登録が確認されました！</h3>
            <p>
                外構相場.comへの登録が完了しました。<br>
                これで、購読者パネルにアクセスして<br class="sp">注文のリストを確認できる<br class="sp">ようになりました。<br>
                登録したメールアドレスとパスワードを<br class="sp">使用して購読者パネルに<br class="sp">アクセスしてください。
            </p>
            <div class="button">
                <a href="https://gaiko-souba-net.icdl.tokyo/sign-in" rel="noopener noreferrer" target="_blank">加入者パネルにアクセスする</a>
            </div>
            <h3>これがあなたの購読プランです。</h3>
            <p>タイプ - {{ $plan }}</p>
            <p>お支払い - {{ $price }}</p>
            <p class="align-left">ご質問やご不明な点がございましたら、info@gaiko-souba.net または 0000-2222-1111-211 まで<br>お気軽にお問い合わせください。</p>
        
            <div class="contact-address align-left">
                <p>一番、
                    <br>外構相場.com
                    <br>info@gaiko-souba.net
                    <br>0000-2222-1111-211
                    <br>No.1、住所例。</p>
            </div>
        </div>
    </div>
</body>
</html>