<?php

namespace Tests\Unit;

use App\Support\Elision;
use PHPUnit\Framework\TestCase;

class ElisionTest extends TestCase
{
    public function test_de_elides_before_a_vowel_or_an_h(): void
    {
        $this->assertSame("d'EduGes", Elision::de('EduGes'));
        $this->assertSame("d'une solution", Elision::de('une solution'));
        $this->assertSame("d'Hébergement", Elision::de('Hébergement'));
        $this->assertSame("d'Étude", Elision::de('Étude'));
        $this->assertSame("d'Yves", Elision::de('Yves'));
    }

    public function test_de_keeps_the_full_word_before_a_consonant(): void
    {
        $this->assertSame('de MediGes SST', Elision::de('MediGes SST'));
        $this->assertSame('de Compiniya', Elision::de(' Compiniya '));
        $this->assertSame('de', Elision::de(''));
    }
}
