<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

        $this->call('UserTableSeeder');
        $this->command->info('User table seeded!');

		// $this->call('UserTableSeeder');
	}

}

class UserTableSeeder extends Seeder {

    public function run()
    {
        DB::table('users')->delete();

        User::create(['name' => '4Fun', 'email' => '4Fun@pbo.org','club_id' => '30103','password'=>bcrypt('123456')]);
        User::create(['name' => '4GHENT BC', 'email' => '4GHENTBC@pbo.org','club_id' => '30004','password'=>bcrypt('123456')]);
        User::create(['name' => 'AALSTERSE BC', 'email' => 'AALSTERSEBC@pbo.org','club_id' => '30024','password'=>bcrypt('123456')]);
        User::create(['name' => 'ASSENEEDSE BC', 'email' => 'ASSENEEDSEBC@pbo.org','club_id' => '30039','password'=>bcrypt('123456')]);
        User::create(['name' => 'ASTENE BC', 'email' => 'ASTENEBC@pbo.org','club_id' => '30094','password'=>bcrypt('123456')]);
        User::create(['name' => 'BAD 86 vzw', 'email' => 'BAD86vzw@pbo.org','club_id' => '30077','password'=>bcrypt('123456')]);
        User::create(['name' => 'BADLOVE', 'email' => 'BADLOVE@pbo.org','club_id' => '30019','password'=>bcrypt('123456')]);
        User::create(['name' => 'BADMINTON BUGGENHOUT VZW', 'email' => 'BADMINTONBUGGENHOUTVZW@pbo.org','club_id' => '30026','password'=>bcrypt('123456')]);
        User::create(['name' => 'Badmintonclub Latem-De Pinte', 'email' => 'BadmintonclubLatem-DePinte@pbo.org','club_id' => '30007','password'=>bcrypt('123456')]);
        User::create(['name' => 'BC SALAMANDER', 'email' => 'BCSALAMANDER@pbo.org','club_id' => '30090','password'=>bcrypt('123456')]);
        User::create(['name' => 'BC VLA-BAD', 'email' => 'BCVLA-BAD@pbo.org','club_id' => '30049','password'=>bcrypt('123456')]);
        User::create(['name' => 'BC WAARSCHOOT', 'email' => 'BCWAARSCHOOT@pbo.org','club_id' => '30095','password'=>bcrypt('123456')]);
        User::create(['name' => 'BEVEREN BC', 'email' => 'BEVERENBC@pbo.org','club_id' => '30005','password'=>bcrypt('123456')]);
        User::create(['name' => 'BRAKEL BC', 'email' => 'BRAKELBC@pbo.org','club_id' => '30096','password'=>bcrypt('123456')]);
        User::create(['name' => 'B-TEAM BADMINTON', 'email' => 'B-TEAMBADMINTON@pbo.org','club_id' => '30088','password'=>bcrypt('123456')]);
        User::create(['name' => 'CHALLENGE WETTEREN BC', 'email' => 'CHALLENGEWETTERENBC@pbo.org','club_id' => '30022','password'=>bcrypt('123456')]);
        User::create(['name' => 'DANLIE HAMME BC', 'email' => 'DANLIEHAMMEBC@pbo.org','club_id' => '30034','password'=>bcrypt('123456')]);
        User::create(['name' => 'DE MINTONS BC', 'email' => 'DEMINTONSBC@pbo.org','club_id' => '30001','password'=>bcrypt('123456')]);
        User::create(['name' => 'DE MOTTEKLOPPERS BC', 'email' => 'DEMOTTEKLOPPERSBC@pbo.org','club_id' => '30037','password'=>bcrypt('123456')]);
        User::create(['name' => 'DE NIEUWE SNAAR BC', 'email' => 'DENIEUWESNAARBC@pbo.org','club_id' => '30089','password'=>bcrypt('123456')]);
        User::create(['name' => 'De Pluimgewichten', 'email' => 'DePluimgewichten@pbo.org','club_id' => '30102','password'=>bcrypt('123456')]);
        User::create(['name' => 'DE SHUTTLES', 'email' => 'DESHUTTLES@pbo.org','club_id' => '30101','password'=>bcrypt('123456')]);
        User::create(['name' => 'DE VEERKRACHT BC', 'email' => 'DEVEERKRACHTBC@pbo.org','club_id' => '30099','password'=>bcrypt('123456')]);
        User::create(['name' => 'DE WALLABIES BRAKEL', 'email' => 'DEWALLABIESBRAKEL@pbo.org','club_id' => '30086','password'=>bcrypt('123456')]);
        User::create(['name' => 'DE WUBBOS BC', 'email' => 'DEWUBBOSBC@pbo.org','club_id' => '30097','password'=>bcrypt('123456')]);
        User::create(['name' => 'DE WUITEN BC', 'email' => 'DEWUITENBC@pbo.org','club_id' => '30032','password'=>bcrypt('123456')]);
        User::create(['name' => 'DEINZE BC', 'email' => 'DEINZEBC@pbo.org','club_id' => '30030','password'=>bcrypt('123456')]);
        User::create(['name' => 'DENDERLEEUW BC', 'email' => 'DENDERLEEUWBC@pbo.org','club_id' => '30066','password'=>bcrypt('123456')]);
        User::create(['name' => 'DRIVE BC (OVL)', 'email' => 'DRIVEBC-OVL@pbo.org','club_id' => '30020','password'=>bcrypt('123456')]);
        User::create(['name' => 'DYNAMIC LEDE', 'email' => 'DYNAMICLEDE@pbo.org','club_id' => '30067','password'=>bcrypt('123456')]);
        User::create(['name' => 'EIKENLO', 'email' => 'EIKENLO@pbo.org','club_id' => '30038','password'=>bcrypt('123456')]);
        User::create(['name' => 'FLEE SHUTTLE BK', 'email' => 'FLEESHUTTLEBK@pbo.org','club_id' => '30033','password'=>bcrypt('123456')]);
        User::create(['name' => 'Gavere BC', 'email' => 'GavereBC@pbo.org','club_id' => '30104','password'=>bcrypt('123456')]);
        User::create(['name' => 'GENTSE BC', 'email' => 'GENTSEBC@pbo.org','club_id' => '30009','password'=>bcrypt('123456')]);
        User::create(['name' => 'GERAARDSBERGEN BC', 'email' => 'GERAARDSBERGENBC@pbo.org','club_id' => '30061','password'=>bcrypt('123456')]);
        User::create(['name' => 'HOGE WAL BC', 'email' => 'HOGEWALBC@pbo.org','club_id' => '30080','password'=>bcrypt('123456')]);
        User::create(['name' => 'Just For Fun', 'email' => 'JustForFun@pbo.org','club_id' => '30106','password'=>bcrypt('123456')]);
        User::create(['name' => 'KLEIN PLUIMPJE', 'email' => 'KLEINPLUIMPJE@pbo.org','club_id' => '30087','password'=>bcrypt('123456')]);
        User::create(['name' => 'LANDEGEM BC', 'email' => 'LANDEGEMBC@pbo.org','club_id' => '30050','password'=>bcrypt('123456')]);
        User::create(['name' => 'LOKERSE BC', 'email' => 'LOKERSEBC@pbo.org','club_id' => '30042','password'=>bcrypt('123456')]);
        User::create(['name' => 'MALDEGEM BC', 'email' => 'MALDEGEMBC@pbo.org','club_id' => '30063','password'=>bcrypt('123456')]);
        User::create(['name' => 'MARIA - AALTER BC', 'email' => 'MARIA-AALTERBC@pbo.org','club_id' => '30079','password'=>bcrypt('123456')]);
        User::create(['name' => 'MERELBEKE BC', 'email' => 'MERELBEKEBC@pbo.org','club_id' => '30028','password'=>bcrypt('123456')]);
        User::create(['name' => 'NILSTON BC', 'email' => 'NILSTONBC@pbo.org','club_id' => '30045','password'=>bcrypt('123456')]);
        User::create(['name' => 'OUDEGEM BC', 'email' => 'OUDEGEMBC@pbo.org','club_id' => '30071','password'=>bcrypt('123456')]);
        User::create(['name' => 'PLUIMKES TROEF BC', 'email' => 'PLUIMKESTROEFBC@pbo.org','club_id' => '30098','password'=>bcrypt('123456')]);
        User::create(['name' => 'PLUIMPLUKKERS BC', 'email' => 'PLUIMPLUKKERSBC@pbo.org','club_id' => '30027','password'=>bcrypt('123456')]);
        User::create(['name' => 'POLDERBOS BC', 'email' => 'POLDERBOSBC@pbo.org','club_id' => '30069','password'=>bcrypt('123456')]);
        User::create(['name' => 'SENTSE BADMINTON Club', 'email' => 'SENTSEBADMINTONClub@pbo.org','club_id' => '30091','password'=>bcrypt('123456')]);
        User::create(['name' => 'SHUTTLE STARS PUYENBROECK', 'email' => 'SHUTTLESTARSPUYENBROECK@pbo.org','club_id' => '30085','password'=>bcrypt('123456')]);
        User::create(['name' => 'SMASH FOR FUN', 'email' => 'SMASHFORFUN@pbo.org','club_id' => '30076','password'=>bcrypt('123456')]);
        User::create(['name' => 'SNAAR AF', 'email' => 'SNAARAF@pbo.org','club_id' => '30036','password'=>bcrypt('123456')]);
        User::create(['name' => 'SNOASE SMASHERS', 'email' => 'SNOASESMASHERS@pbo.org','club_id' => '30105','password'=>bcrypt('123456')]);
        User::create(['name' => 'STEKENE BC', 'email' => 'STEKENEBC@pbo.org','club_id' => '30010','password'=>bcrypt('123456')]);
        User::create(['name' => 'T BROEKSKEN', 'email' => 'TBROEKSKEN@pbo.org','club_id' => '30093','password'=>bcrypt('123456')]);
        User::create(['name' => 'TEMSE BC', 'email' => 'TEMSEBC@pbo.org','club_id' => '30002','password'=>bcrypt('123456')]);
        User::create(['name' => 'THE FLYING SHUTTLES BC', 'email' => 'THEFLYINGSHUTTLESBC@pbo.org','club_id' => '30008','password'=>bcrypt('123456')]);
        User::create(['name' => 'VAG EVERGEM BC', 'email' => 'VAGEVERGEMBC@pbo.org','club_id' => '30025','password'=>bcrypt('123456')]);
        User::create(['name' => 'WIT-WIT BC', 'email' => 'WIT-WITBCpbo.org','club_id' => '30055','password'=>bcrypt('123456')]);
    }

}