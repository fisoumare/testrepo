<?php

namespace Tests\Unit;

use App\Support\Typo;
use PHPUnit\Framework\TestCase;

/** Typographie française : insécables avant la ponctuation haute, dans les guillemets et entre nombre et unité. */
class TypoTest extends TestCase
{
    private const NBSP = "\u{00A0}";

    private const NNBSP = "\u{202F}";

    public function test_colon_gets_a_no_break_space_and_urls_are_left_alone(): void
    {
        $this->assertSame('Horaires'.self::NBSP.': 8'.self::NBSP.'h', Typo::fr('Horaires : 8 h'));
        $this->assertSame('Voir https://compiniya.com : ici', str_replace(self::NBSP, ' ', Typo::fr('Voir https://compiniya.com : ici')));
        $this->assertSame('https://compiniya.com', Typo::fr('https://compiniya.com'));
    }

    public function test_high_punctuation_gets_a_narrow_no_break_space(): void
    {
        $this->assertSame(
            'Oui'.self::NNBSP.'? Non'.self::NNBSP.'! Peut-être'.self::NNBSP.'; vraiment'.self::NNBSP.'?!',
            Typo::fr('Oui ? Non ! Peut-être ; vraiment ?!'),
        );
    }

    public function test_french_quotes_are_padded_with_no_break_spaces(): void
    {
        $this->assertSame('Dire «'.self::NBSP.'bonjour'.self::NBSP.'».', Typo::fr('Dire « bonjour ».'));
    }

    public function test_numbers_stick_to_their_unit_and_thousands_are_grouped(): void
    {
        $this->assertSame('TVA 18'.self::NBSP.'%', Typo::fr('TVA 18 %'));
        $this->assertSame('8'.self::NBSP.'h'.self::NBSP.'30 – 17'.self::NBSP.'h'.self::NBSP.'30', Typo::fr('8 h 30 – 17 h 30'));
        $this->assertSame('2'.self::NNBSP.'500'.self::NNBSP.'000'.self::NBSP.'GNF HT', Typo::fr('2 500 000 GNF HT'));
        $this->assertSame('10'.self::NBSP.'Go', Typo::fr('10 Go'));
        // Un mot qui commence par une lettre d'unité n'est pas une unité.
        $this->assertSame('48 heures, 3 mois, 5 pages', Typo::fr('48 heures, 3 mois, 5 pages'));
    }

    public function test_html_tags_and_attributes_are_preserved(): void
    {
        $html = '<a href="https://x.test/a b" title="Dire : non">lien</a> : suite ; <b>fin ?</b>';

        $this->assertSame(
            '<a href="https://x.test/a b" title="Dire : non">lien</a>'.self::NBSP.': suite'.self::NNBSP.'; <b>fin'.self::NNBSP.'?</b>',
            Typo::fr($html),
        );
    }

    public function test_is_idempotent_and_handles_empty_values(): void
    {
        $once = Typo::fr('Prix : 18 % ; « oui » ?');

        $this->assertSame($once, Typo::fr($once));
        $this->assertSame('', Typo::fr(null));
        $this->assertSame('', Typo::fr(''));
    }
}
