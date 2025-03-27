<?php

namespace PoMoh\Adapter\MoFile;

class Expression {

    const OP_CHARS = '|&><!=%?:';

    const NUM_CHARS = '0123456789';

    protected static array $op_precedence = [
        '%'  => 6,

        '<'  => 5,
        '<=' => 5,
        '>'  => 5,
        '>=' => 5,

        '==' => 4,
        '!=' => 4,

        '&&' => 3,

        '||' => 2,

        '?:' => 1,
        '?'  => 1,

        '('  => 0,
        ')'  => 0,
    ];

    protected array $tokens = [];

    protected array $cache = [];

    public function __construct(string $expr) {
        $this->parse($expr);
    }

    public function get(int $num): int {
        if ( isset( $this->cache[ $num ] ) ) {
            return $this->cache[ $num ];
        }
        $this->cache[ $num ] = $this->execute( $num );
        return $this->cache[ $num ];
    }

    protected function parse(string $expr): void {
        $pos = 0;
        $len = strlen( $expr );

        # Convert infix operators to postfix using the shunting-yard algorithm.
        $output = array();
        $stack  = array();
        while ( $pos < $len ) {
            $next = substr( $expr, $pos, 1 );

            switch ( $next ) {
                # Ignore whitespace.
                case ' ':
                case "\t":
                    ++$pos;
                    break;

                # Variable (n).
                case 'n':
                    $output[] = array( 'var' );
                    ++$pos;
                    break;

                # Parentheses.
                case '(':
                    $stack[] = $next;
                    ++$pos;
                    break;

                case ')':
                    $found = false;
                    while ( ! empty( $stack ) ) {
                        $o2 = $stack[ count( $stack ) - 1 ];
                        if ( '(' !== $o2 ) {
                            $output[] = array( 'op', array_pop( $stack ) );
                            continue;
                        }

                        # Discard open paren.
                        array_pop( $stack );
                        $found = true;
                        break;
                    }

                    if ( ! $found ) {
                        throw new ExpressionException( 'Mismatched parentheses' );
                    }

                    ++$pos;
                    break;

                # Operators.
                case '|':
                case '&':
                case '>':
                case '<':
                case '!':
                case '=':
                case '%':
                case '?':
                    $end_operator = strspn( $expr, self::OP_CHARS, $pos );
                    $operator     = substr( $expr, $pos, $end_operator );
                    if ( ! array_key_exists( $operator, self::$op_precedence ) ) {
                        throw new ExpressionException( sprintf( 'Unknown operator "%s"', $operator ) );
                    }

                    while ( ! empty( $stack ) ) {
                        $o2 = $stack[ count( $stack ) - 1 ];

                        # Ternary is right-associative in C.
                        if ( '?:' === $operator || '?' === $operator ) {
                            if ( self::$op_precedence[ $operator ] >= self::$op_precedence[ $o2 ] ) {
                                break;
                            }
                        } elseif ( self::$op_precedence[ $operator ] > self::$op_precedence[ $o2 ] ) {
                            break;
                        }

                        $output[] = array( 'op', array_pop( $stack ) );
                    }
                    $stack[] = $operator;

                    $pos += $end_operator;
                    break;

                # Ternary "else".
                case ':':
                    $found = false;
                    $s_pos = count( $stack ) - 1;
                    while ( $s_pos >= 0 ) {
                        $o2 = $stack[ $s_pos ];
                        if ( '?' !== $o2 ) {
                            $output[] = array( 'op', array_pop( $stack ) );
                            --$s_pos;
                            continue;
                        }

                        # Replace.
                        $stack[ $s_pos ] = '?:';
                        $found           = true;
                        break;
                    }

                    if ( ! $found ) {
                        throw new ExpressionException( 'Missing starting "?" ternary operator' );
                    }
                    ++$pos;
                    break;

                # Default - number or invalid.
                default:
                    if ( $next >= '0' && $next <= '9' ) {
                        $span     = strspn( $expr, self::NUM_CHARS, $pos );
                        $output[] = array( 'value', intval( substr( $expr, $pos, $span ) ) );
                        $pos     += $span;
                        break;
                    }

                    throw new ExpressionException( sprintf( 'Unknown symbol "%s"', $next ) );
            }
        }

        while ( ! empty( $stack ) ) {
            $o2 = array_pop( $stack );
            if ( '(' === $o2 || ')' === $o2 ) {
                throw new ExpressionException( 'Mismatched parentheses' );
            }

            $output[] = array( 'op', $o2 );
        }

        $this->tokens = $output;
    }

    protected function execute(int $n): int {
        $stack = array();
        $i     = 0;
        $total = count( $this->tokens );
        while ( $i < $total ) {
            $next = $this->tokens[ $i ];
            ++$i;
            if ( 'var' === $next[0] ) {
                $stack[] = $n;
                continue;
            } elseif ( 'value' === $next[0] ) {
                $stack[] = $next[1];
                continue;
            }

            # Only operators left.
            switch ( $next[1] ) {
                case '%':
                    $v2      = array_pop( $stack );
                    $v1      = array_pop( $stack );
                    $stack[] = $v1 % $v2;
                    break;

                case '||':
                    $v2      = array_pop( $stack );
                    $v1      = array_pop( $stack );
                    $stack[] = $v1 || $v2;
                    break;

                case '&&':
                    $v2      = array_pop( $stack );
                    $v1      = array_pop( $stack );
                    $stack[] = $v1 && $v2;
                    break;

                case '<':
                    $v2      = array_pop( $stack );
                    $v1      = array_pop( $stack );
                    $stack[] = $v1 < $v2;
                    break;

                case '<=':
                    $v2      = array_pop( $stack );
                    $v1      = array_pop( $stack );
                    $stack[] = $v1 <= $v2;
                    break;

                case '>':
                    $v2      = array_pop( $stack );
                    $v1      = array_pop( $stack );
                    $stack[] = $v1 > $v2;
                    break;

                case '>=':
                    $v2      = array_pop( $stack );
                    $v1      = array_pop( $stack );
                    $stack[] = $v1 >= $v2;
                    break;

                case '!=':
                    $v2      = array_pop( $stack );
                    $v1      = array_pop( $stack );
                    $stack[] = $v1 !== $v2;
                    break;

                case '==':
                    $v2      = array_pop( $stack );
                    $v1      = array_pop( $stack );
                    $stack[] = $v1 === $v2;
                    break;

                case '?:':
                    $v3      = array_pop( $stack );
                    $v2      = array_pop( $stack );
                    $v1      = array_pop( $stack );
                    $stack[] = $v1 ? $v2 : $v3;
                    break;

                default:
                    throw new ExpressionException( sprintf( 'Unknown operator "%s"', $next[1] ) );
            }
        }

        if ( count( $stack ) !== 1 ) {
            throw new ExpressionException( 'Too many values remaining on the stack' );
        }

        return (int) $stack[0];
    }
}
