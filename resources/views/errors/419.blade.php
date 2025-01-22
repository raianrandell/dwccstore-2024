<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

  <title>419 Page Expired</title>

  <style>
    * {
      -webkit-box-sizing: border-box;
      box-sizing: border-box;
    }

    body {
      padding: 0;
      margin: 0;
    }

    #notfound {
      position: relative;
      height: 100vh;
    }

    #notfound .notfound {
      position: absolute;
      left: 50%;
      top: 50%;
      -webkit-transform: translate(-50%, -50%);
      -ms-transform: translate(-50%, -50%);
      transform: translate(-50%, -50%);
    }

    .notfound {
      max-width: 520px;
      width: 100%;
      line-height: 1.4;
      text-align: center;
    }

    .notfound .notfound-404 {
      position: relative;
      height: 240px;
    }

    .notfound .notfound-404 h1 {
      font-family: 'Montserrat', sans-serif;
      position: absolute;
      left: 50%;
      top: 50%;
      -webkit-transform: translate(-50%, -50%);
      -ms-transform: translate(-50%, -50%);
      transform: translate(-50%, -50%);
      font-size: 252px;
      font-weight: 900;
      margin: 0;
      color: #262626;
      text-transform: uppercase;
      letter-spacing: -40px;
      margin-left: -20px;
    }

    .notfound .notfound-404 h1>span {
      text-shadow: -8px 0 0 #fff;
    }

    .notfound .notfound-404 h3 {
      font-family: 'Cabin', sans-serif;
      position: relative;
      font-size: 16px;
      font-weight: 700;
      text-transform: uppercase;
      color: #262626;
      margin: 0;
      letter-spacing: 3px;
      padding-left: 6px;
    }

    .notfound h2 {
      font-family: 'Cabin', sans-serif;
      font-size: 20px;
      font-weight: 400;
      text-transform: uppercase;
      color: #000;
      margin-top: 0;
      margin-bottom: 25px;
    }

    .return-btn {
      font-family: 'Cabin', sans-serif;
      display: inline-flex;
      align-items: center;
      margin-top: 20px;
      padding: 10px 25px;
      font-size: 16px;
      font-weight: bold;
      text-transform: uppercase;
      text-decoration: none;
      color: #262626;
      background-color: transparent;
      border: 1px solid #262626;
      transition: background-color 0.3s ease, transform 0.3s ease; /* Added transform transition */
    }

    .return-btn i {
    margin-right: 8px; /* Adds space between the icon and the text */
    font-size: 18px; /* Adjust the icon size */
    }

    .return-btn:hover {
      color: #fff;
      background-color: #444;
      animation: bounce 0.5s infinite alternate;
    }

    @keyframes bounce {
      from {
        transform: translateY(0);
      }
      to {
        transform: translateY(-5px);
      }
    }

    @media only screen and (max-width: 767px) {
      .notfound .notfound-404 {
        height: 200px;
      }

      .notfound .notfound-404 h1 {
        font-size: 200px;
      }
    }

    @media only screen and (max-width: 480px) {
      .notfound .notfound-404 {
        height: 162px;
      }

      .notfound .notfound-404 h1 {
        font-size: 162px;
        height: 150px;
        line-height: 162px;
      }

      .notfound h2 {
        font-size: 16px;
      }
    }
  </style>
  <meta name="robots" content="noindex, follow">
</head>

<script>
    function goBackAndReload() {
        if (document.referrer) {
            window.location.href = document.referrer;
        } else {
            window.location.reload();
        }
    }
</script>

<body>
  <div id="notfound">
    <div class="notfound">
      <div class="notfound-404">
        <h3>Oops! Session Expired</h3>
        <h1><span>4</span><span>1</span><span>9</span></h1>
      </div>
      <h2>Your session has expired. Please return back to the login page.</h2>
      <a href="javascript:void(0);" class="return-btn" onclick="goBackAndReload()">
        <i class="fa fa-arrow-left"></i> Return Back
      </a>
    </div>
  </div>
</body>

</html>