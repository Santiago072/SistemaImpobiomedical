<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Pruebas unitarias de sanitización contra CSV / Excel Formula Injection (CWE-1236).
 */
class FormulaInjectionTest extends TestCase
{
    /**
     * Prueba que los caracteres de inicio de fórmula (=, +, -, @, \t, \r) sean neutralizados con comilla simple.
     */
    public function testSanitizarPrefijosDeFormula(): void
    {
        $payloadIgual = '=SUM(A1:A10)';
        $this->assertSame("'=SUM(A1:A10)", sanitizar_formula_excel($payloadIgual));

        $payloadCmd = "=cmd|'/C calc'!A0";
        $this->assertSame("'=cmd|'/C calc'!A0", sanitizar_formula_excel($payloadCmd));

        $payloadMas = '+100';
        $this->assertSame("'+100", sanitizar_formula_excel($payloadMas));

        $payloadMenos = '-250';
        $this->assertSame("'-250", sanitizar_formula_excel($payloadMenos));

        $payloadArroba = '@SUM(1,2)';
        $this->assertSame("'@SUM(1,2)", sanitizar_formula_excel($payloadArroba));

        $payloadTab = "\tTEXTO";
        $this->assertSame("'\tTEXTO", sanitizar_formula_excel($payloadTab));
    }

    /**
     * Prueba que textos normales y números legítimos no se alteren.
     */
    public function testTextosNormalesPermanecenInalterados(): void
    {
        $this->assertSame("Hospital San Juan de Dios", sanitizar_formula_excel("Hospital San Juan de Dios"));
        $this->assertSame("900535843-3", sanitizar_formula_excel("900535843-3"));
        $this->assertSame("Monitor Multiparámetro Mindray", sanitizar_formula_excel("Monitor Multiparámetro Mindray"));
        $this->assertSame("", sanitizar_formula_excel(""));
    }
}
