<?php

declare(strict_types=1);

namespace Tests\Unit\Publications;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FullTextIndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that FULLTEXT index exists on publications table.
     */
    public function test_publications_table_has_fulltext_index(): void
    {
        // Skip test if not using MySQL (SQLite doesn't support FULLTEXT)
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('FULLTEXT indexes are only supported on MySQL');
        }

        $indexes = DB::select("SHOW INDEX FROM publications WHERE Key_name = 'publications_title_fulltext'");

        $this->assertNotEmpty($indexes, 'FULLTEXT index publications_title_fulltext should exist on publications table');

        // Verify it covers both title and title_low columns
        $columnNames = collect($indexes)->pluck('Column_name')->toArray();
        $this->assertContains('title', $columnNames);
        $this->assertContains('title_low', $columnNames);
    }

    /**
     * Test that FULLTEXT index exists on authors table.
     */
    public function test_authors_table_has_fulltext_index(): void
    {
        // Skip test if not using MySQL (SQLite doesn't support FULLTEXT)
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('FULLTEXT indexes are only supported on MySQL');
        }

        $indexes = DB::select("SHOW INDEX FROM authors WHERE Key_name = 'authors_name_fulltext'");

        $this->assertNotEmpty($indexes, 'FULLTEXT index authors_name_fulltext should exist on authors table');

        // Verify it covers both author and author_low columns
        $columnNames = collect($indexes)->pluck('Column_name')->toArray();
        $this->assertContains('author', $columnNames);
        $this->assertContains('author_low', $columnNames);
    }
}
