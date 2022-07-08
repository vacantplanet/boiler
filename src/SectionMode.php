<?php

declare(strict_types=1);

namespace Conia\Boiler;


enum SectionMode
{
    case Assign;
    case Append;
    case Prepend;
    case Closed;
}
