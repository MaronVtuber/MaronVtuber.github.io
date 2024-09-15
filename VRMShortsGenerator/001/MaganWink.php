<?php
header('Cross-Origin-Opener-Policy: same-origin');
header('Cross-Origin-Embedder-Policy: require-corp');
?>
<!doctype html>
<html lang="ja" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="//cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.10.1/dist/ffmpeg.min.js"></script>

    <link href="./bootstrap.min.css" rel="stylesheet">

    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .b-example-divider {
            height: 3rem;
            background-color: rgba(0, 0, 0, .1);
            border: solid rgba(0, 0, 0, .15);
            border-width: 1px 0;
            box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
        }

        .b-example-vr {
            flex-shrink: 0;
            width: 1.5rem;
            height: 100vh;
        }

        .bi {
            vertical-align: -.125em;
            fill: currentColor;
        }

        .nav-scroller {
            position: relative;
            z-index: 2;
            height: 2.75rem;
            overflow-y: hidden;
        }

            .nav-scroller .nav {
                display: flex;
                flex-wrap: nowrap;
                padding-bottom: 1rem;
                margin-top: -1px;
                overflow-x: auto;
                text-align: center;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }
    </style>


    <!-- Custom styles for this template -->
    <link href="./cover.css" rel="stylesheet">
</head>

<body class="d-flex text-center text-bg-dark" style="height: 1200px;">
    <img src="" style="
  position: absolute;
  height: 100%;
  z-index: -11;
  left: 50%;
  top: 5rem;
  transform: translateX(-50%);
  filter: brightness(0.5);
  ">
    <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
        <header class="mb-auto">
            <div>

                <h3 class="float-md-start mb-0">VRM Shorts Generator Beta</h3>

                <nav class="nav nav-masthead justify-content-center float-md-end">
                    <a class="nav-link fw-bold py-1 px-0 active" aria-current="page">魔眼ウインク</a>
                </nav>
            </div>
        </header>

        <main class="px-3">
            <h1 id="status">Unityアプリ起動中</h1>
            <p class="lead" id="information">データを読み込んでいます。しばらくお待ちください</p>
            <div class="spinner-border text-light" role="status" id="loading">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="lead">
                <a href="#" class="btn btn-lg btn-light fw-bold border-white bg-white" style="display: none;" id="button"
                   onclick="document.querySelector('#file').click()">アバターを選んで作成</a>
            </p>

            <p class="lead">

            </p>
        </main>

        <footer class="mt-auto text-white-50">
            <p>
                Cover template for <a href="https://getbootstrap.com/" class="text-white">Bootstrap</a>, by <a href="https://twitter.com/mdo" class="text-white">@mdo</a>.
            </p>
        </footer>
    </div>

    <div id="unity-container" class="unity-desktop" style="visibility: hidden; position: absolute; z-index: -100;">
        <canvas id="unity-canvas" width=1080 height=1920></canvas>
        <div id="unity-loading-bar">
            <div id="unity-logo"></div>
            <div id="unity-progress-bar-empty">
                <div id="unity-progress-bar-full"></div>
            </div>
        </div>
        <div id="unity-warning"> </div>
        <div id="unity-footer">
            <div id="unity-webgl-logo"></div>
            <div id="unity-fullscreen-button"></div>
            <div id="unity-build-title">WebGLVRMShort</div>
        </div>
    </div>
    <script>
        var container = document.querySelector("#unity-container");
        var canvas = document.querySelector("#unity-canvas");
        var loadingBar = document.querySelector("#unity-loading-bar");
        var progressBarFull = document.querySelector("#unity-progress-bar-full");
        var fullscreenButton = document.querySelector("#unity-fullscreen-button");
        var warningBanner = document.querySelector("#unity-warning");

        // Shows a temporary message banner/ribbon for a few seconds, or
        // a permanent error message on top of the canvas if type=='error'.
        // If type=='warning', a yellow highlight color is used.
        // Modify or remove this function to customize the visually presented
        // way that non-critical warnings and error messages are presented to the
        // user.
        function unityShowBanner(msg, type) {
            function updateBannerVisibility() {
                warningBanner.style.display = warningBanner.children.length ? 'block' : 'none';
            }
            var div = document.createElement('div');
            div.innerHTML = msg;
            warningBanner.appendChild(div);
            if (type == 'error') div.style = 'background: red; padding: 10px;';
            else {
                if (type == 'warning') div.style = 'background: yellow; padding: 10px;';
                setTimeout(function () {
                    warningBanner.removeChild(div);
                    updateBannerVisibility();
                }, 5000);
            }
            updateBannerVisibility();
        }
        var buildUrl = "Build";
        var loaderUrl = buildUrl + "/Build.loader.js";
        var config = {
            dataUrl: buildUrl + "/Build.data",
            frameworkUrl: buildUrl + "/Build.framework.js",
            codeUrl: buildUrl + "/Build.wasm",
            streamingAssetsUrl: "StreamingAssets",
            companyName: "DefaultCompany",
            productName: "WebGLVRMShort",
            productVersion: "0.1",
            showBanner: unityShowBanner,
        };

        canvas.style.width = "1080px";
        canvas.style.height = "1920px";


        loadingBar.style.display = "block";

        var script = document.createElement("script");
        script.src = loaderUrl;
        script.onload = () => {
            createUnityInstance(canvas, config, (progress) => {
                progressBarFull.style.width = 100 * progress + "%";
            }).then((unityInstance) => {
                window.unityInstance = unityInstance;
                setTimeout(() => {
                    setStateToSelectModel();
                }, 2000);

                loadingBar.style.display = "none";
                fullscreenButton.onclick = () => {
                    unityInstance.SetFullscreen(1);
                };
            }).catch((message) => {
                alert(message);
            });
        };
        document.body.appendChild(script);
    </script>

    <input file type="file" style="display: none;" id="file" onchange="handleFile()" accept=".vrm" />

    <script src="./UnityRecorder.js"></script>
</body>

</html>
