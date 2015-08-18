<?php

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

        //$this->call('UserTableSeeder');
        $this->call('UserTableAddHocAdding');
        $this->command->info('User table seeded!');

		// $this->call('UserTableSeeder');
	}

}

class UserTableSeeder extends Seeder {

    public function run()
    {
        DB::table('lf_users')->delete();

        User::create(['name' => 'DE MINTONS BC', 'email' => 'mintons@pandora.be','club_id' => '30001','password'=>bcrypt('12345678')]);
        User::create(['name' => 'TEMSE BC', 'email' => 'secretaris@bctemse.be','club_id' => '30002','password'=>bcrypt('12345678')]);
        User::create(['name' => '4GHENT BC', 'email' => 'an.thys@telenet.be','club_id' => '30004','password'=>bcrypt('12345678')]);
        User::create(['name' => 'BEVEREN BC', 'email' => 'webmaster@badminton-beveren.be','club_id' => '30005','password'=>bcrypt('12345678')]);
        User::create(['name' => 'Badmintonclub Latem-De Pinte', 'email' => 'secretariaat@badmintonclublatemdepinte.be','club_id' => '30007','password'=>bcrypt('12345678')]);
        User::create(['name' => 'THE FLYING SHUTTLES BC', 'email' => 'julietollenaere@gmail.com','club_id' => '30008','password'=>bcrypt('12345678')]);
        User::create(['name' => 'GENTSE BC', 'email' => 'secretaris@gentsebc.be','club_id' => '30009','password'=>bcrypt('12345678')]);
        User::create(['name' => 'STEKENE BC', 'email' => 'godefroidkim@hotmail.com','club_id' => '30010','password'=>bcrypt('12345678')]);
        User::create(['name' => 'DRIVE BC (OVL)', 'email' => 'info@drivebc.be','club_id' => '30020','password'=>bcrypt('12345678')]);
        User::create(['name' => 'CHALLENGE WETTEREN BC', 'email' => 'gertdewaele@hotmail.com','club_id' => '30022','password'=>bcrypt('12345678')]);
        User::create(['name' => 'AALSTERSE BC', 'email' => 'secretariaat@aalsterse-bc.be','club_id' => '30024','password'=>bcrypt('12345678')]);
        User::create(['name' => 'VAG EVERGEM BC', 'email' => 'secr.vagevergembc@gmail.com','club_id' => '30025','password'=>bcrypt('12345678')]);
        User::create(['name' => 'BADMINTON BUGGENHOUT VZW', 'email' => 'babbuggenhout@gmail.com','club_id' => '30026','password'=>bcrypt('12345678')]);
        User::create(['name' => 'PLUIMPLUKKERS BC', 'email' => 'info@pluimplukkers.be','club_id' => '30027','password'=>bcrypt('12345678')]);
        User::create(['name' => 'Badmintonclub Deinze', 'email' => 'bcdeinze@edpnet.be','club_id' => '30030','password'=>bcrypt('12345678')]);
        User::create(['name' => 'FLEE SHUTTLE BK', 'email' => 'fleeshuttle@gmail.com','club_id' => '30033','password'=>bcrypt('12345678')]);
        User::create(['name' => 'DANLIE HAMME BC', 'email' => 'danliehamme@gmail.com','club_id' => '30034','password'=>bcrypt('12345678')]);
        User::create(['name' => 'EIKENLO', 'email' => 'jp.de.vlieger@telenet.be','club_id' => '30038','password'=>bcrypt('12345678')]);
        User::create(['name' => 'LOKERSE BC', 'email' => 'buik01@hotmail.com','club_id' => '30042','password'=>bcrypt('12345678')]);
        User::create(['name' => 'NILSTON BC', 'email' => 'vandelsen-deboeck@telenet.be','club_id' => '30045','password'=>bcrypt('12345678')]);
        User::create(['name' => 'BC VLA-BAD', 'email' => 'bestuur@vlabad.be','club_id' => '30049','password'=>bcrypt('12345678')]);
        User::create(['name' => 'LANDEGEM BC', 'email' => 'vantornhoutluc@skynet.be','club_id' => '30050','password'=>bcrypt('12345678')]);
        User::create(['name' => 'WIT-WIT BC', 'email' => 'joris.vandenhoucke@telenet.be','club_id' => '30055','password'=>bcrypt('12345678')]);
        User::create(['name' => 'GERAARDSBERGEN BC', 'email' => 'secretaris@bc-geraardsbergen.be','club_id' => '30061','password'=>bcrypt('12345678')]);
        User::create(['name' => 'DENDERLEEUW BC', 'email' => 'daviddebolle@hotmail.com','club_id' => '30066','password'=>bcrypt('12345678')]);
        User::create(['name' => 'DYNAMIC LEDE', 'email' => 'info@dynamiclede.be','club_id' => '30067','password'=>bcrypt('12345678')]);
        User::create(['name' => 'OUDEGEM BC', 'email' => 'info@bcoudegem.be','club_id' => '30071','password'=>bcrypt('12345678')]);
        User::create(['name' => 'SMASH FOR FUN', 'email' => 'erik.d.vr@smashforfun.be','club_id' => '30076','password'=>bcrypt('12345678')]);
        User::create(['name' => 'HOGE WAL BC', 'email' => 'deco.dekey@skynet.be','club_id' => '30080','password'=>bcrypt('12345678')]);
        User::create(['name' => 'SHUTTLE STARS PUYENBROECK', 'email' => 'info@shuttlestars.be','club_id' => '30085','password'=>bcrypt('12345678')]);
        User::create(['name' => 'DE WALLABIES BRAKEL', 'email' => 'els.roelandt@hotmail.com','club_id' => '30086','password'=>bcrypt('12345678')]);
        User::create(['name' => 'DE NIEUWE SNAAR BC', 'email' => 'isabelle_heylbroeck@hotmail.com','club_id' => '30089','password'=>bcrypt('12345678')]);
        User::create(['name' => 'SENTSE BADMINTON Club vzw', 'email' => 'info@sentsebadminton.be','club_id' => '30091','password'=>bcrypt('12345678')]);
        User::create(['name' => 'BC WAARSCHOOT', 'email' => 'bcwaarschoot@gmail.com','club_id' => '30095','password'=>bcrypt('12345678')]);
        User::create(['name' => 'BRAKEL BC', 'email' => 'marcbosman@telenet.be','club_id' => '30096','password'=>bcrypt('12345678')]);
    }

}

class UserTableAddHocAdding extends Seeder {

    public function run()
    {
        User::create(['name' => 'STEKENE BC', 'email' => 'toon.bouchier@telenet.be','club_id' => '30010','password'=>bcrypt('12345678')]);
        User::create(['name' => 'CHALLENGE WETTEREN BC', 'email' => 'administrator@bcchallenge.be','club_id' => '30022','password'=>bcrypt('12345678')]);
        User::create(['name' => 'GENTSE BC', 'email' => 'thomas.dekeyser@gmail.com','club_id' => '30009','password'=>bcrypt('12345678')]);
    }

}