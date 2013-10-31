--TEST--
json2
--FILE--
<?php
error_reporting(E_ALL);

$s = ' { "A" : [ 1 , 2 ] , "B" : [ 3.1 , 4.2e10 ]   } ';
$s =<<<END
{"menu": {
    "header": "SVG Viewer",
    "items": [
        {"id": "Open"},
        {"id": "OpenNew", "label": "Open New"},
        null,
        {"id": "ZoomIn", "label": "Zoom In"},
        {"id": "ZoomOut", "label": "Zoom Out"},
        {"id": "OriginalView", "label": "Original View"},
        null,
        {"id": "Quality"},
        {"id": "Pause"},
        {"id": "Mute"},
        null,
        {"id": "Find", "label": "Find..."},
        {"id": "FindAgain", "label": "Find Again"},
        {"id": "Copy"},
        {"id": "CopyAgain", "label": "Copy Again"},
        {"id": "CopySVG", "label": "Copy SVG"},
        {"id": "ViewSVG", "label": "View SVG"},
        {"id": "ViewSource", "label": "View Source"},
        {"id": "SaveAs", "label": "Save As"},
        null,
        {"id": "Help"},
        {"id": "About", "label": "About Adobe CVG Viewer..."}
    ]
}}
END;

for ($n=0;$n<1000;$n++) {
  $a = json_decode($s, 0);
}
print_r($a);

?>
--EXPECT--
stdClass Object
(
    [menu] => stdClass Object
        (
            [header] => SVG Viewer
            [items] => Array
                (
                    [0] => stdClass Object
                        (
                            [id] => Open
                        )

                    [1] => stdClass Object
                        (
                            [id] => OpenNew
                            [label] => Open New
                        )

                    [2] => 
                    [3] => stdClass Object
                        (
                            [id] => ZoomIn
                            [label] => Zoom In
                        )

                    [4] => stdClass Object
                        (
                            [id] => ZoomOut
                            [label] => Zoom Out
                        )

                    [5] => stdClass Object
                        (
                            [id] => OriginalView
                            [label] => Original View
                        )

                    [6] => 
                    [7] => stdClass Object
                        (
                            [id] => Quality
                        )

                    [8] => stdClass Object
                        (
                            [id] => Pause
                        )

                    [9] => stdClass Object
                        (
                            [id] => Mute
                        )

                    [10] => 
                    [11] => stdClass Object
                        (
                            [id] => Find
                            [label] => Find...
                        )

                    [12] => stdClass Object
                        (
                            [id] => FindAgain
                            [label] => Find Again
                        )

                    [13] => stdClass Object
                        (
                            [id] => Copy
                        )

                    [14] => stdClass Object
                        (
                            [id] => CopyAgain
                            [label] => Copy Again
                        )

                    [15] => stdClass Object
                        (
                            [id] => CopySVG
                            [label] => Copy SVG
                        )

                    [16] => stdClass Object
                        (
                            [id] => ViewSVG
                            [label] => View SVG
                        )

                    [17] => stdClass Object
                        (
                            [id] => ViewSource
                            [label] => View Source
                        )

                    [18] => stdClass Object
                        (
                            [id] => SaveAs
                            [label] => Save As
                        )

                    [19] => 
                    [20] => stdClass Object
                        (
                            [id] => Help
                        )

                    [21] => stdClass Object
                        (
                            [id] => About
                            [label] => About Adobe CVG Viewer...
                        )

                )

        )

)
