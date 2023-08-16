<?php

namespace App\Enums;

enum Enum : int
{
    case Waiting = 1;
    case Assigned = 2;
    case InProgress = 3;
    case Completed = 4;
}
