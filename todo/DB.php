<?php
class DB
{

    const dsn = 'mysql:dbname=todo_2021_7;host=localhost;port=3306';
    const username = 'root';
    const password = '';

    public static function h($mes)
    {
        return htmlspecialchars($mes, ENT_QUOTES, "UTF-8");
    }
}
