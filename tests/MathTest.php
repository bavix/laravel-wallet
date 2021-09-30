<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Internal\MathInterface;
use Brick\Math\BigInteger;
use Brick\Math\Exception\NumberFormatException;

/**
 * @internal
 */
class MathTest extends TestCase
{
    /**
     * @dataProvider invalidProvider
     */
    public function testAbsInvalid(string $value): void
    {
        $this->expectException(NumberFormatException::class);

        /** @var MathInterface $provider */
        $provider = app(MathInterface::class);
        $provider->abs($value);
    }

    public function testAbs(): void
    {
        /** @var MathInterface $provider */
        $provider = app(MathInterface::class);

        // int
        self::assertEquals(123, $provider->abs(123));
        self::assertEquals(123, $provider->abs(-123));

        // float
        self::assertEquals(0, $provider->abs(.0));
        self::assertEquals(123, $provider->abs(123.0));
        self::assertEquals(123.11, $provider->abs(123.11));
        self::assertEquals(123.11, $provider->abs(-123.11));

        // string
        if (!method_exists(BigInteger::class, 'parse')) {
            // brick/math 0.9+
            self::assertEquals(123, $provider->abs('123.'));
            self::assertEquals(.11, $provider->abs('.11'));
        }

        self::assertEquals(123.11, $provider->abs('123.11'));
        self::assertEquals(123.11, $provider->abs('-123.11'));
    }

    public function testCompare(): void
    {
        /** @var MathInterface $provider */
        $provider = app(MathInterface::class);

        // int
        self::assertEquals(0, $provider->compare(1, 1));
        self::assertEquals(-1, $provider->compare(1, 2));
        self::assertEquals(1, $provider->compare(2, 1));

        // float
        self::assertEquals(0, $provider->compare(1.33, 1.33));
        self::assertEquals(-1, $provider->compare(1.44, 2));
        self::assertEquals(1, $provider->compare(2, 1.44));

        // string
        self::assertEquals(0, $provider->compare('1.33', '1.33'));
        self::assertEquals(-1, $provider->compare('1.44', '2'));
        self::assertEquals(1, $provider->compare('2', '1.44'));
    }

    public function testAdd(): void
    {
        /** @var MathInterface $provider */
        $provider = app(MathInterface::class);

        // int
        self::assertEquals(0, $provider->compare($provider->add(1, 5), 6));
        self::assertEquals(0, $provider->compare($provider->add(-1, 5), 4));

        // float
        self::assertEquals(0, $provider->compare($provider->add(1.17, 4.83), 6.));
        self::assertEquals(0, $provider->compare($provider->add(-1.44, 5.43), 3.99));

        self::assertEquals(
            0,
            $provider->compare(
                $provider->add('4.331733759839529271053448625299468628', 1.4),
                '5.731733759839529271053448625299468628'
            )
        );

        self::assertEquals(
            0,
            $provider->compare(
                $provider->add('5.731733759839529271053448625299468628', '-5.731733759839529271053448625299468627'),
                '0.000000000000000000000000000000000001'
            )
        );
    }

    public function testSub(): void
    {
        /** @var MathInterface $provider */
        $provider = app(MathInterface::class);

        // int
        self::assertEquals(-4, $provider->sub(1, 5));
        self::assertEquals(-6, $provider->sub(-1, 5));

        // float
        self::assertEquals(-3.66, $provider->sub(1.17, 4.83));
        self::assertEquals(-6.87, $provider->sub(-1.44, 5.43));

        self::assertEquals(
            0,
            $provider->compare(
                $provider->sub('4.331733759839529271053448625299468628', 1.4),
                '2.931733759839529271053448625299468628'
            )
        );

        self::assertEquals(
            0,
            $provider->compare(
                $provider->sub('5.731733759839529271053448625299468628', '5.731733759839529271053448625299468627'),
                '0.000000000000000000000000000000000001'
            )
        );
    }

    public function testDiv(): void
    {
        /** @var MathInterface $provider */
        $provider = app(MathInterface::class);

        // int
        self::assertEquals(0.2, $provider->div(1, 5));
        self::assertEquals(-0.2, $provider->div(-1, 5));

        // float
        self::assertEquals(0.24223602484472, $provider->div(1.17, 4.83));
        self::assertEquals(-0.26519337016574, $provider->div(-1.44, 5.43));

        self::assertEquals(
            0,
            $provider->compare(
                $provider->div('4.331733759839529271053448625299468628', 1.4),
                '3.0940955427425209078953204466424775914285714285714285714285714285'
            )
        );

        self::assertEquals(
            0,
            $provider->compare(
                $provider->div('5.731733759839529271053448625299468628', '5.731733759839529271053448625299468627'),
                '1.0000000000000000000000000000000000001744672802157504419105369811'
            )
        );
    }

    public function testMul(): void
    {
        /** @var MathInterface $provider */
        $provider = app(MathInterface::class);

        // int
        self::assertEquals(5, $provider->mul(1, 5));
        self::assertEquals(-5, $provider->mul(-1, 5));

        // float
        self::assertEquals(5.6511, $provider->mul(1.17, 4.83));
        self::assertEquals(-7.8192, $provider->mul(-1.44, 5.43));

        self::assertEquals(
            0,
            $provider->compare(
                $provider->mul('4.331733759839529271053448625299468628', 1.4),
                '6.0644272637753409794748280754192560792000000000000000000000000000'
            )
        );

        self::assertEquals(
            0,
            $provider->compare(
                $provider->mul('5.731733759839529271053448625299468628', '5.731733759839529271053448625299468627'),
                '32.8527718936841866108362353549577464458763784076112941028307058338'
            )
        );
    }

    public function testPow(): void
    {
        /** @var MathInterface $provider */
        $provider = app(MathInterface::class);

        // int
        self::assertEquals(1, $provider->pow(1, 5));
        self::assertEquals(-1, $provider->pow(-1, 5));

        // float
        self::assertEquals(1.87388721, $provider->pow(1.17, 4));
        self::assertEquals(-6.1917364224, $provider->pow(-1.44, 5));

        self::assertEquals(
            0,
            $provider->compare(
                $provider->pow('4.331733759839529271053448625299468628', 14),
                '818963567.1194514424328910747572247977826032927674623819207642247854744523'
            )
        );

        self::assertEquals(
            0,
            $provider->compare(
                $provider->pow('5.731733759839529271053448625299468628', 6),
                '35458.1485207464760293448564751702377579632773756221209731837301291644'
            )
        );
    }

    public function testCeil(): void
    {
        /** @var MathInterface $provider */
        $provider = app(MathInterface::class);

        // positive
        // int
        self::assertEquals(
            35458,
            $provider->ceil(35458)
        );

        // float
        self::assertEquals(35458, $provider->ceil('35458.00000000'));
        self::assertEquals(
            35459,
            $provider->ceil(35458.0000001)
        );
        self::assertEquals(
            35459,
            $provider->ceil(35458.4)
        );
        self::assertEquals(
            35459,
            $provider->ceil(35458.5)
        );
        self::assertEquals(
            35459,
            $provider->ceil(35458.6)
        );

        // string
        self::assertEquals(
            35459,
            $provider->ceil('35458.1485207464760293448564751702377579632773756221209731837301291644')
        );

        // negative
        // int
        self::assertEquals(
            -35458,
            $provider->ceil(-35458)
        );

        // float
        self::assertEquals(
            -35458,
            $provider->ceil(-35458.0000001)
        );
        self::assertEquals(
            -35458,
            $provider->ceil(-35458.4)
        );
        self::assertEquals(
            -35458,
            $provider->ceil(-35458.5)
        );
        self::assertEquals(
            -35458,
            $provider->ceil(-35458.6)
        );

        // string
        self::assertEquals(
            -35458,
            $provider->ceil('-35458.1485207464760293448564751702377579632773756221209731837301291644')
        );
    }

    public function testFloor(): void
    {
        /** @var MathInterface $provider */
        $provider = app(MathInterface::class);

        // positive
        // int
        self::assertEquals(
            35458,
            $provider->floor(35458)
        );

        // float
        self::assertEquals(35458, $provider->floor('35458.00000000'));
        self::assertEquals(
            35458,
            $provider->floor(35458.0000001)
        );
        self::assertEquals(
            35458,
            $provider->floor(35458.4)
        );
        self::assertEquals(
            35458,
            $provider->floor(35458.5)
        );
        self::assertEquals(
            35458,
            $provider->floor(35458.6)
        );

        // string
        self::assertEquals(
            35458,
            $provider->floor('35458.1485207464760293448564751702377579632773756221209731837301291644')
        );

        // negative
        // int
        self::assertEquals(
            -35458,
            $provider->floor(-35458)
        );

        // float
        self::assertEquals(
            -35459,
            $provider->floor(-35458.0000001)
        );
        self::assertEquals(
            -35459,
            $provider->floor(-35458.4)
        );
        self::assertEquals(
            -35459,
            $provider->floor(-35458.5)
        );
        self::assertEquals(
            -35459,
            $provider->floor(-35458.6)
        );

        // string
        self::assertEquals(
            -35459,
            $provider->floor('-35458.1485207464760293448564751702377579632773756221209731837301291644')
        );
    }

    public function testRound(): void
    {
        /** @var MathInterface $provider */
        $provider = app(MathInterface::class);

        // positive
        // int
        self::assertEquals(
            35458,
            $provider->round(35458)
        );

        // float
        self::assertEquals(35458, $provider->round('35458.00000000'));
        self::assertEquals(
            35458,
            $provider->round(35458.0000001)
        );
        self::assertEquals(
            35458,
            $provider->round(35458.4)
        );
        self::assertEquals(
            35459,
            $provider->round(35458.5)
        );
        self::assertEquals(
            35459,
            $provider->round(35458.6)
        );

        // string
        self::assertEquals(
            35458,
            $provider->round('35458.1485207464760293448564751702377579632773756221209731837301291644')
        );

        // negative
        // int
        self::assertEquals(
            -35458,
            $provider->round(-35458)
        );

        // float
        self::assertEquals(
            -35458,
            $provider->round(-35458.0000001)
        );
        self::assertEquals(
            -35458,
            $provider->round(-35458.4)
        );
        self::assertEquals(
            -35459,
            $provider->round(-35458.5)
        );
        self::assertEquals(
            -35459,
            $provider->round(-35458.6)
        );

        // string
        self::assertEquals(
            -35458,
            $provider->round('-35458.1485207464760293448564751702377579632773756221209731837301291644')
        );
    }

    public function invalidProvider(): array
    {
        return [
            ['.'],
            ['hello'],
            ['--121'],
            ['---121'],
        ];
    }
}
