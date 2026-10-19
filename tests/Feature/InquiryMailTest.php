<?php

namespace Tests\Feature;

use App\Mail\InquiryConfirmation;
use App\Mail\InquiryReceived;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Tests\TestCase;

/** E-mails de notification et d'accusé de réception : les textes saisis par le visiteur ne sont jamais interprétés (HTML ni Markdown). */
class InquiryMailTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private const INJECTED_MESSAGE = "# Titre injecté\n\n![pix](https://tracker.example/p.gif)\n[lien](https://evil.example)\n\n    code indenté\n<script>alert(1)</script>\n\nLigne 1\nLigne 2 | pipe\n</pre><b>gras</b>";

    private function inquiry(): Inquiry
    {
        return Inquiry::createWithReference([
            'type' => Inquiry::TYPE_QUOTE,
            'name' => 'Nom [lien](https://evil.example)',
            'organization' => '*Organisation*',
            'email' => 'client@example.com',
            'phone' => '+224 600 00 00 00',
            'subject' => 'Demande de devis en ligne',
            'message' => self::INJECTED_MESSAGE,
            'items' => [[
                'service_id' => 1,
                'number' => 'PREST-2026-0001',
                'name' => 'Site | *vitrine*',
                'unit' => 'Forfait',
                'quantity' => 2,
                'unit_price' => 100,
                'total' => 200,
            ]],
            'total_ht' => 200,
        ]);
    }

    /** @return list<Mailable> */
    private function mailables(Inquiry $inquiry): array
    {
        return [new InquiryReceived($inquiry), new InquiryConfirmation($inquiry)];
    }

    public function test_visitor_message_is_rendered_verbatim_and_never_interpreted(): void
    {
        foreach ($this->mailables($this->inquiry()) as $mailable) {
            $mailable
                // Rien n'est interprété : ni titre, ni image de suivi, ni lien, ni HTML, ni bloc de code.
                ->assertDontSeeInHtml('<h1>Titre', false)
                ->assertDontSeeInHtml('src="https://tracker.example', false)
                ->assertDontSeeInHtml('href="https://evil.example', false)
                ->assertDontSeeInHtml('<script>', false)
                ->assertDontSeeInHtml('<b>gras</b>', false)
                ->assertDontSeeInHtml('<code>', false)
                // Le texte est conservé tel quel, retours à la ligne et indentation compris.
                ->assertSeeInHtml('# Titre injecté')
                ->assertSeeInHtml("Ligne 1\nLigne 2 | pipe", false)
                ->assertSeeInHtml("\n    code indenté\n", false)
                ->assertSeeInHtml('<pre', false)
                // Version texte : le message brut, sans balise.
                ->assertSeeInText('# Titre injecté')
                ->assertSeeInText("Ligne 1\nLigne 2 | pipe")
                ->assertSeeInText("\n    code indenté\n")
                ->assertDontSeeInText('<pre');
        }
    }

    public function test_names_and_catalogue_lines_are_escaped_for_markdown(): void
    {
        foreach ($this->mailables($this->inquiry()) as $mailable) {
            $mailable
                ->assertDontSeeInHtml('href="https://evil.example', false)
                ->assertSeeInHtml('Nom [lien](https://evil.example)')
                ->assertDontSeeInHtml('<em>vitrine</em>', false)
                ->assertDontSeeInHtml('<em>Organisation</em>', false)
                // La barre du nom de prestation ne crée pas de colonne supplémentaire dans le tableau.
                ->assertSeeInHtml('Site | *vitrine*')
                ->assertSeeInHtml('(Forfait)')
                ->assertSeeInHtml('PREST-2026-0001');
        }
    }

    public function test_received_mail_links_to_the_back_office_and_replies_to_the_visitor(): void
    {
        $inquiry = $this->inquiry();
        $mailable = new InquiryReceived($inquiry);

        $mailable
            ->assertSeeInHtml('/admin/inquiries/'.$inquiry->getKey(), false)
            ->assertSeeInHtml('Ouvrir dans le back-office')
            ->assertSeeInHtml($inquiry->reference);

        $this->assertSame('client@example.com', $mailable->envelope()->replyTo[0]->address);
    }
}
