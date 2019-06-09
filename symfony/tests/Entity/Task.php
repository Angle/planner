<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testBullet()
    {
        // CASE 1: THE QUERY WEEK IS THE CURRENT WEEK
        // CASE 1.1: TASK STATUS: OPEN
        // CASE 1.1.1: OPEN TIME IN CURRENT WEEK

        // CASE 1.1.2: OPEN TIME IN THE PAST

        // CASE 1.1.3: OPEN TIME IN THE FUTURE


        // CASE 1.2: TASK STATUS: CLOSE
        // CASE 1.2.1: OPEN TIME IN CURRENT WEEK, CLOSE TIME IN CURRENT WEEK

        // CASE 1.2.2: OPEN TIME IN CURRENT WEEK, CLOSE TIME IN PAST
        // this case should never happen

        // CASE 1.2.3: OPEN TIME IN CURRENT WEEK, CLOSE TIME IN FUTURE


        // CASE 1.2.4: OPEN TIME IN THE PAST, CLOSE TIME IN CURRENT WEEK

        // CASE 1.2.5: OPEN TIME IN THE PAST, CLOSE TIME IN PAST
        // this task should not appear on the list

        // CASE 1.2.6: OPEN TIME IN THE PAST, CLOSE TIME IN THE FUTURE


        // CASE 1.2.4: OPEN TIME IN THE PAST, CLOSE TIME IN CURRENT WEEK

        // CASE 1.2.5: OPEN TIME IN THE PAST, CLOSE TIME IN PAST
        // this task should not appear on the list

        // CASE 1.2.6: OPEN TIME IN THE PAST, CLOSE TIME IN THE FUTURE



        // CASE 2: THE QUERY WEEK IS PAST (OLDER THAN THE CURRENT WEEK)


        // CASE 3: THE QUERY WEEK IS FUTURE (NEWER THAN THE CURRENT WEEK)


        // TESTING OPEN TASKS
        $task = new Task();
        $task->setOpenYearNumber(2019);
        $task->setOpenWeekNumber(25);

        $calculator = new Calculator();
        $result = $calculator->add(30, 12);

        // assert that your calculator added the numbers correctly!
        $this->assertEquals(42, $result);
    }

    public function testFlag()
    {

    }
}