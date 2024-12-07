<?php

    // Hooks will always use GET method.
    // Assuming that you structured your hook link like this: http://someremoteurl.com/test.php?phone={{phone}}&message={{message}}&time={{date.time}}
    // You should be able to parse the variables like this:

    $request = $_GET;

    echo $request["phone"];
    echo $request["message"];
    echo $request["time"];

    // you can do anything with these variables. save to your database or launch an automated task on your end.