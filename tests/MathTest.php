<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Simple\BCMath;
use Bavix\Wallet\Simple\Math;

class MathTest extends TestCase
{

    /**
     * @dataProvider dataProvider
     * @param string $class
     * @return void
     */
    public function testAbs(string $class): void
    {
        /**
         * @var Mathable $provider
         */
        $provider = app($class);

        // not number
        self::assertEquals($provider->abs('.'), 0);
        self::assertEquals($provider->abs('hello'), 0);
        self::assertEquals($provider->abs('--121'), 0);
        self::assertEquals($provider->abs('---121'), 0);

        // int
        self::assertEquals($provider->abs(123), 123);
        self::assertEquals($provider->abs(-123), 123);

        // float
        self::assertEquals($provider->abs(.0), 0);
        self::assertEquals($provider->abs(123.0), 123);
        self::assertEquals($provider->abs(123.11), 123.11);
        self::assertEquals($provider->abs(-123.11), 123.11);

        // string
        self::assertEquals($provider->abs('123.'), 123);
        self::assertEquals($provider->abs('.11'), .11);
        self::assertEquals($provider->abs('123.11'), 123.11);
        self::assertEquals($provider->abs('-123.11'), 123.11);
    }

    /**
     * @dataProvider dataProvider
     * @param string $class
     * @return void
     */
    public function testCompare(string $class): void
    {
        /**
         * @var Mathable $provider
         */
        $provider = app($class);

        // int
        self::assertEquals($provider->compare(1, 1), 0);
        self::assertEquals($provider->compare(1, 2), -1);
        self::assertEquals($provider->compare(2, 1), 1);

        // float
        self::assertEquals($provider->compare(1.33, 1.33), 0);
        self::assertEquals($provider->compare(1.44, 2), -1);
        self::assertEquals($provider->compare(2, 1.44), 1);

        // string
        self::assertEquals($provider->compare('1.33', '1.33'), 0);
        self::assertEquals($provider->compare('1.44', '2'), -1);
        self::assertEquals($provider->compare('2', '1.44'), 1);
    }

    /**
     * @dataProvider dataProvider
     * @param string $class
     * @return void
     */
    public function testAdd(string $class): void
    {
        /**
         * @var Mathable $provider
         */
        $provider = app($class);

        // int
        self::assertEquals($provider->compare($provider->add(1, 5), 6), 0);
        self::assertEquals($provider->compare($provider->add(-1, 5), 4), 0);

        // float
        self::assertEquals($provider->compare($provider->add(1.17, 4.83), 6.), 0);
        self::assertEquals($provider->compare($provider->add(-1.44, 5.43), 3.99), 0);

        if ($provider instanceof BCMath) {
            self::assertEquals(
                $provider->compare(
                    $provider->add('4.331733759839529271053448625299468628', 1.4),
                    '5.731733759839529271053448625299468628'
                ),
                0
            );

            self::assertEquals(
                $provider->compare(
                    $provider->add('5.731733759839529271053448625299468628', '-5.731733759839529271053448625299468627'),
                    '0.000000000000000000000000000000000001'
                ),
                0
            );
        }
    }

    /**
     * @dataProvider dataProvider
     * @param string $class
     * @return void
     */
    public function testSub(string $class): void
    {
        /**
         * @var Mathable $provider
         */
        $provider = app($class);

        // int
        self::assertEquals($provider->sub(1, 5), -4);
        self::assertEquals($provider->sub(-1, 5), -6);

        // float
        self::assertEquals($provider->sub(1.17, 4.83), -3.66);
        self::assertEquals($provider->sub(-1.44, 5.43), -6.87);

        if ($provider instanceof BCMath) {
            self::assertEquals(
                $provider->compare(
                    $provider->sub('4.331733759839529271053448625299468628', 1.4),
                    '2.931733759839529271053448625299468628'
                ),
                0
            );

            self::assertEquals(
                $provider->compare(
                    $provider->sub('5.731733759839529271053448625299468628', '5.731733759839529271053448625299468627'),
                    '0.000000000000000000000000000000000001'
                ),
                0
            );
        }
    }

    /**
     * @dataProvider dataProvider
     * @param string $class
     * @return void
     */
    public function testDiv(string $class): void
    {
        /**
         * @var Mathable $provider
         */
        $provider = app($class);

        // int
        self::assertEquals($provider->div(1, 5), 0.2);
        self::assertEquals($provider->div(-1, 5), -0.2);

        // float
        self::assertEquals($provider->div(1.17, 4.83), 0.24223602484472);
        self::assertEquals($provider->div(-1.44, 5.43), -0.26519337016574);

        if ($provider instanceof BCMath) {
            self::assertEquals(
                $provider->compare(
                    $provider->div('4.331733759839529271053448625299468628', 1.4),
                    '3.0940955427425209078953204466424775914285714285714285714285714285'
                ),
                0
            );

            self::assertEquals(
                $provider->compare(
                    $provider->div('5.731733759839529271053448625299468628', '5.731733759839529271053448625299468627'),
                    '1.0000000000000000000000000000000000001744672802157504419105369811'
                ),
                0
            );
        }
    }

    /**
     * @dataProvider dataProvider
     * @param string $class
     * @return void
     */
    public function testMul(string $class): void
    {
        /**
         * @var Mathable $provider
         */
        $provider = app($class);

        // int
        self::assertEquals($provider->mul(1, 5), 5);
        self::assertEquals($provider->mul(-1, 5), -5);

        // float
        self::assertEquals($provider->mul(1.17, 4.83), 5.6511);
        self::assertEquals($provider->mul(-1.44, 5.43), -7.8192);

        if ($provider instanceof BCMath) {
            self::assertEquals(
                $provider->compare(
                    $provider->mul('4.331733759839529271053448625299468628', 1.4),
                    '6.0644272637753409794748280754192560792000000000000000000000000000'
                ),
                0
            );

            self::assertEquals(
                $provider->compare(
                    $provider->mul('5.731733759839529271053448625299468628', '5.731733759839529271053448625299468627'),
                    '32.8527718936841866108362353549577464458763784076112941028307058338'
                ),
                0
            );
        }
    }

    /**
     * @dataProvider dataProvider
     * @param string $class
     * @return void
     */
    public function testPow(string $class): void
    {
        /**
         * @var Mathable $provider
         */
        $provider = app($class);

        // int
        self::assertEquals($provider->pow(1, 5), 1);
        self::assertEquals($provider->pow(-1, 5), -1);

        // float
        self::assertEquals($provider->pow(1.17, 4), 1.87388721);
        self::assertEquals($provider->pow(-1.44, 5), -6.1917364224);

        if ($provider instanceof BCMath) {
            self::assertEquals(
                $provider->compare(
                    $provider->pow('4.331733759839529271053448625299468628', 14),
                    '818963567.1194514424328910747572247977826032927674623819207642247854744523'
                ),
                0
            );

            self::assertEquals(
                $provider->compare(
                    $provider->pow('5.731733759839529271053448625299468628', 6),
                    '35458.1485207464760293448564751702377579632773756221209731837301291644'
                ),
                0
            );
        }
    }

    /**
     * @dataProvider dataProvider
     * @param string $class
     * @return void
     */
    public function testCeil(string $class): void
    {
        /**
         * @var Mathable $provider
         */
        $provider = app($class);

        // positive
        // int
        self::assertEquals(
            $provider->ceil(35458),
            35458
        );

        // float
        self::assertEquals($provider->ceil('35458.00000000'), 35458);
        self::assertEquals(
            $provider->ceil(35458.0000001),
            35459
        );
        self::assertEquals(
            $provider->ceil(35458.4),
            35459
        );
        self::assertEquals(
            $provider->ceil(35458.5),
            35459
        );
        self::assertEquals(
            $provider->ceil(35458.6),
            35459
        );

        // string
        self::assertEquals(
            $provider->ceil('35458.1485207464760293448564751702377579632773756221209731837301291644'),
            35459
        );

        // negative
        // int
        self::assertEquals(
            $provider->ceil(-35458),
            -35458
        );

        // float
        self::assertEquals(
            $provider->ceil(-35458.0000001),
            -35458
        );
        self::assertEquals(
            $provider->ceil(-35458.4),
            -35458
        );
        self::assertEquals(
            $provider->ceil(-35458.5),
            -35458
        );
        self::assertEquals(
            $provider->ceil(-35458.6),
            -35458
        );

        // string
        self::assertEquals(
            $provider->ceil('-35458.1485207464760293448564751702377579632773756221209731837301291644'),
            -35458
        );
    }

    /**
     * @dataProvider dataProvider
     * @param string $class
     * @return void
     */
    public function testFloor(string $class): void
    {
        /**
         * @var Mathable $provider
         */
        $provider = app($class);

        // positive
        // int
        self::assertEquals(
            $provider->floor(35458),
            35458
        );

        // float
        self::assertEquals($provider->floor('35458.00000000'), 35458);
        self::assertEquals(
            $provider->floor(35458.0000001),
            35458
        );
        self::assertEquals(
            $provider->floor(35458.4),
            35458
        );
        self::assertEquals(
            $provider->floor(35458.5),
            35458
        );
        self::assertEquals(
            $provider->floor(35458.6),
            35458
        );

        // string
        self::assertEquals(
            $provider->floor('35458.1485207464760293448564751702377579632773756221209731837301291644'),
            35458
        );

        // negative
        // int
        self::assertEquals(
            $provider->floor(-35458),
            -35458
        );

        // float
        self::assertEquals(
            $provider->floor(-35458.0000001),
            -35459
        );
        self::assertEquals(
            $provider->floor(-35458.4),
            -35459
        );
        self::assertEquals(
            $provider->floor(-35458.5),
            -35459
        );
        self::assertEquals(
            $provider->floor(-35458.6),
            -35459
        );

        // string
        self::assertEquals(
            $provider->floor('-35458.1485207464760293448564751702377579632773756221209731837301291644'),
            -35459
        );
    }

    /**
     * @dataProvider dataProvider
     * @param string $class
     * @return void
     */
    public function testRound(string $class): void
    {
        /**
         * @var Mathable $provider
         */
        $provider = app($class);

        // positive
        // int
        self::assertEquals(
            $provider->round(35458),
            35458
        );

        // float
        self::assertEquals($provider->round('35458.00000000'), 35458);
        self::assertEquals(
            $provider->round(35458.0000001),
            35458
        );
        self::assertEquals(
            $provider->round(35458.4),
            35458
        );
        self::assertEquals(
            $provider->round(35458.5),
            35459
        );
        self::assertEquals(
            $provider->round(35458.6),
            35459
        );

        // string
        self::assertEquals(
            $provider->round('35458.1485207464760293448564751702377579632773756221209731837301291644'),
            35458
        );

        // negative
        // int
        self::assertEquals(
            $provider->round(-35458),
            -35458
        );

        // float
        self::assertEquals(
            $provider->round(-35458.0000001),
            -35458
        );
        self::assertEquals(
            $provider->round(-35458.4),
            -35458
        );
        self::assertEquals(
            $provider->round(-35458.5),
            -35459
        );
        self::assertEquals(
            $provider->round(-35458.6),
            -35459
        );

        // string
        self::assertEquals(
            $provider->round('-35458.1485207464760293448564751702377579632773756221209731837301291644'),
            -35458
        );
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        $providers = [[Math::class]];

        if (extension_loaded('bcmath')) {
            $providers[] = [BCMath::class];
        }

        return $providers;
    }

}
