<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow, noarchive">
        <link rel="icon" href="{{asset('favicon.ico')}}">
        <title>{{ config('app.name', 'Laravel') }}</title>

        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: Arial, sans-serif;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 15px;
            }

            main {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .text-centner {
                text-align: center;
            }
        </style>
    </head>

    <body>
       <main>
           <section class="text-centner">
               <div class="container">
                   <h1>Nice try =)</h1>
               </div>
           </section>
       </main>
    </body>
</html>
