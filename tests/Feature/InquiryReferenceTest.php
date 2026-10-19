<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Numérotation des références (DEM-AAAA-0001, MSG-AAAA-0001) et protection contre deux envois simultanés. */
class InquiryReferenceTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function attributes(string $type = Inquiry::TYPE_CONTACT): array
    {
        return [
            'type' => $type,
            'name' => 'Prénom Nom',
            'email' => 'client@example.com',
            'subject' => 'Assistance',
            'message' => 'Un message de test suffisamment long pour le formulaire.',
        ];
    }

    public function test_references_are_numbered_per_type_and_year(): void
    {
        $year = now()->format('Y');

        $this->assertSame("MSG-{$year}-0001", Inquiry::createWithReference($this->attributes())->reference);
        $this->assertSame("DEM-{$year}-0001", Inquiry::createWithReference($this->attributes(Inquiry::TYPE_QUOTE))->reference);
        $this->assertSame("MSG-{$year}-0002", Inquiry::createWithReference($this->attributes())->reference);
        $this->assertSame("MSG-{$year}-0003", Inquiry::nextReference(Inquiry::TYPE_CONTACT));
    }

    /** Deux requêtes simultanées peuvent calculer la même référence : la seconde insertion est retentée avec la suivante. */
    public function test_a_duplicate_reference_is_retried_with_the_next_number(): void
    {
        $year = now()->format('Y');
        $first = Inquiry::createWithReference($this->attributes());

        // Simule la course : le prochain enregistrement reçoit (une seule fois) une référence déjà prise.
        $collisions = 1;
        Inquiry::creating(function (Inquiry $inquiry) use (&$collisions, $first) {
            if ($collisions-- > 0) {
                $inquiry->reference = $first->reference;
            }
        });

        $second = Inquiry::createWithReference($this->attributes());

        $this->assertSame("MSG-{$year}-0002", $second->reference);
        $this->assertDatabaseCount('inquiries', 2);
        $this->assertSame(-1, $collisions, 'Le premier essai a bien provoqué la collision.');
    }

    public function test_persistent_collisions_give_up_after_the_last_attempt(): void
    {
        $first = Inquiry::createWithReference($this->attributes());

        $attempts = 0;
        Inquiry::creating(function (Inquiry $inquiry) use (&$attempts, $first) {
            $attempts++;
            $inquiry->reference = $first->reference;
        });

        try {
            Inquiry::createWithReference($this->attributes());
            $this->fail('Une violation de contrainte unique persistante doit finir par remonter.');
        } catch (UniqueConstraintViolationException) {
            $this->assertSame(Inquiry::REFERENCE_ATTEMPTS, $attempts);
            $this->assertDatabaseCount('inquiries', 1);
        }
    }
}
