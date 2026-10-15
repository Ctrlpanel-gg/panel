<?php

namespace App\Constants;

/**
 * MySQL BIGINT limits and application-specific constraints
 * 
 * BIGINT SIGNED: -9,223,372,036,854,775,808 to 9,223,372,036,854,775,807
 * BIGINT UNSIGNED: 0 to 18,446,744,073,709,551,615
 * 
 * For credits: Since the value will be multiplied by 1000 before storage,
 * the maximum input value is floor(9223372036854775807 / 1000) = 9223372036854775
 */
class MysqlLimits
{
    const CREDITS_MIN = 0;
    const CREDITS_MAX = 9223372036854775;
    
    const SERVER_LIMIT_MIN = 0;
    const SERVER_LIMIT_MAX = 1000000;
}