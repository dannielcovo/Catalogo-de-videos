<?php

namespace Tests\Feature\Models\Video\Upload;

use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;

class UploadFilesTest extends TestCase
{
    private $obj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new UploadFilesStub();
        UploadFilesStub::dropTable();
        UploadFilesStub::makeTable();
    }

    public function testMakeOldFilesOnSaving()
    {
        // not exist
        $this->obj->fill([
            'name' => 'test',
            'filme' => 'filmeOld.mp4',
            'banner' => 'banner.jpg',
            'trailer' => 'trailer.mp4',
        ]);

        $this->obj->save();

        $this->assertCount(0, $this->obj->oldFiles);

        //testing update
        $this->obj->update([
            'name' => 'test_name2',
            'filme' => 'FilmeNew.mp4'
        ]);

        //verify oldFile is filme => filmeOld
        $this->assertEqualsCanonicalizing(['filmeOld.mp4'], $this->obj->oldFiles);
    }

    public function testMakeOldFileNullOnSaving()
    {
        $this->obj->fill([
            'name' => 'test',
            'filme' => null,
            'banner' => null,
            'trailler' => null,
        ]);
        $this->obj->save();

        $this->obj->update([
            'name' => 'test_name2',
            'filme' => 'FilmeNew.mp4',
        ]);

        /* test not delete null files */
        $this->assertEqualsCanonicalizing([], $this->obj->oldFiles);
    }
}
