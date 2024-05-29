<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Enums;

enum ProgrammingLanguage: string
{
    case Java = 'Java';
    case Python = 'Python';
    case Javascript = 'Javascript';
    case PHP = 'PHP';
    case CSharp = 'C#';
    case Dart = 'Dart';
}
