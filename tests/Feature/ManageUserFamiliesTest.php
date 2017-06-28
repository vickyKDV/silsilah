<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ManageUserFamiliesTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_update_their_father()
    {
        $user = $this->loginAsUser();
        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->seeElement('input', ['name' => 'set_father']);

        $this->submitForm('set_father_button', [
            'set_father' => 'Nama Ayah',
        ]);

        $this->seeInDatabase('users', [
            'nickname' => 'Nama Ayah',
        ]);

        $this->assertEquals('Nama Ayah', $user->fresh()->father->nickname);
    }

    /** @test */
    public function user_can_update_their_mother()
    {
        $user = $this->loginAsUser();
        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->seeElement('input', ['name' => 'set_mother']);

        $this->submitForm('set_mother_button', [
            'set_mother' => 'Nama Ibu',
        ]);

        $this->seeInDatabase('users', [
            'nickname' => 'Nama Ibu',
        ]);

        $this->assertEquals('Nama Ibu', $user->fresh()->mother->nickname);
    }

    /** @test */
    public function user_can_add_childrens()
    {
        $user = $this->loginAsUser(['gender_id' => 1]);
        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->click('Tambah Anak');
        $this->seeElement('input', ['name' => 'add_child_name']);
        $this->seeElement('input', ['name' => 'add_child_gender_id']);
        $this->seeElement('select', ['name' => 'add_child_parent_id']);

        $this->submitForm('Tambah Anak', [
            'add_child_name' => 'Nama Anak 1',
            'add_child_gender_id' => 1,
            'add_child_parent_id' => '',
        ]);

        $this->seeInDatabase('users', [
            'nickname' => 'Nama Anak 1',
            'gender_id' => 1,
            'father_id' => $user->id,
            'mother_id' => null,
            'parent_id' => null,
        ]);
    }

    /** @test */
    public function user_can_add_childrens_with_parent_id_if_exist()
    {
        $husband = factory(User::class)->states('male')->create();
        $wife = factory(User::class)->states('female')->create();
        $husband->addWife($wife);

        $marriageId = $husband->wifes->first()->pivot->id;
        $this->actingAs($husband);

        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->click('Tambah Anak');
        $this->seeElement('input', ['name' => 'add_child_name']);
        $this->seeElement('input', ['name' => 'add_child_gender_id']);
        $this->seeElement('select', ['name' => 'add_child_parent_id']);

        $this->submitForm('Tambah Anak', [
            'add_child_name' => 'Nama Anak 1',
            'add_child_gender_id' => 1,
            'add_child_parent_id' => $marriageId,
        ]);

        $this->seeInDatabase('users', [
            'nickname' => 'Nama Anak 1',
            'gender_id' => 1,
            'father_id' => $husband->id,
            'mother_id' => $wife->id,
        ]);
    }

    /** @test */
    public function user_can_set_wife()
    {
        $user = $this->loginAsUser(['gender_id' => 1]);
        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->seeElement('input', ['name' => 'set_wife']);

        $this->submitForm('set_wife_button', [
            'set_wife' => 'Nama Istri',
        ]);

        $this->seeInDatabase('users', [
            'nickname' => 'Nama Istri',
            'gender_id' => 2,
        ]);

        $wife = User::orderBy('id', 'desc')->first();

        $this->seeInDatabase('couples', [
            'husband_id' => $user->id,
            'wife_id' => $wife->id,
        ]);
    }

    /** @test */
    public function user_can_set_husband()
    {
        $user = $this->loginAsUser(['gender_id' => 2]);
        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->seeElement('input', ['name' => 'set_husband']);


        $this->submitForm('set_husband_button', [
            'set_husband' => 'Nama Suami',
        ]);

        $this->seeInDatabase('users', [
            'nickname' => 'Nama Suami',
            'gender_id' => 1,
        ]);

        $husband = User::orderBy('id', 'desc')->first();

        $this->seeInDatabase('couples', [
            'husband_id' => $husband->id,
            'wife_id' => $user->id,
        ]);
    }

    /** @test */
    public function user_can_pick_father_from_existing_user()
    {
        $user = $this->loginAsUser();
        $father = factory(User::class)->states('male')->create();

        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->seeElement('input', ['name' => 'set_father']);
        $this->seeElement('select', ['name' => 'set_father_id']);

        $this->submitForm('set_father_button', [
            'set_father' => '',
            'set_father_id' => $father->id,
        ]);

        $this->assertEquals($father->nickname, $user->fresh()->father->nickname);
    }

    /** @test */
    public function user_can_pick_mother_from_existing_user()
    {
        $user = $this->loginAsUser();
        $mother = factory(User::class)->states('female')->create();

        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->seeElement('input', ['name' => 'set_mother']);
        $this->seeElement('select', ['name' => 'set_mother_id']);

        $this->submitForm('set_mother_button', [
            'set_mother' => '',
            'set_mother_id' => $mother->id,
        ]);

        $this->assertEquals($mother->nickname, $user->fresh()->mother->nickname);
    }

    /** @test */
    public function user_can_pick_wife_from_existing_user()
    {
        $user = $this->loginAsUser(['gender_id' => 1]);
        $wife = factory(User::class)->states('female')->create();

        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->seeElement('input', ['name' => 'set_wife']);
        $this->seeElement('select', ['name' => 'set_wife_id']);

        $this->submitForm('set_wife_button', [
            'set_wife' => '',
            'set_wife_id' => $wife->id,
        ]);

        $this->seeInDatabase('couples', [
            'husband_id' => $user->id,
            'wife_id' => $wife->id,
        ]);
    }

    /** @test */
    public function user_can_pick_husband_from_existing_user()
    {
        $user = $this->loginAsUser(['gender_id' => 2]);
        $husband = factory(User::class)->states('male')->create();

        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->seeElement('input', ['name' => 'set_husband']);
        $this->seeElement('select', ['name' => 'set_husband_id']);

        $this->submitForm('set_husband_button', [
            'set_husband' => '',
            'set_husband_id' => $husband->id,
        ]);

        $this->seeInDatabase('couples', [
            'husband_id' => $husband->id,
            'wife_id' => $user->id,
        ]);
    }
}
