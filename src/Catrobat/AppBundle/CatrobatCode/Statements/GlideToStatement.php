<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class GlideToStatement extends Statement
{
    const BEGIN_STRING = "glide (";
    const END_STRING = ")<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

}

?>
