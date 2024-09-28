    <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('first_name')->after('program_id');
            $table->string('last_name')->after('first_name');
            $table->string('email')->after('last_name');
            $table->integer('tickets')->after('email');
        });
    }
    
    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'email', 'tickets']);
        });
    }
    
};
