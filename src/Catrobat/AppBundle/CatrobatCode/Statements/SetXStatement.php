<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class SetXStatement extends BaseSetToStatement
{
    const BEGIN_STRING = "X";
    const END_STRING = ")<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }
}

?>
