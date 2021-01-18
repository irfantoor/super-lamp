<?php

use IrfanTOOR\Test;
use IrfanTOOR\SuperLampCommand;
use IrfanTOOR\Command;

class SuperLampCommandTest extends Test
{
    function testInstance()
    {
        $cmd = new SuperLampCommand();
        $this->assertInstanceOf(SuperLampCommand::class, $cmd);
        $this->assertInstanceOf(Command::class, $cmd);
    }
}
