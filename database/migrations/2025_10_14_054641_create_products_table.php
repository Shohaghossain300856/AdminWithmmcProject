    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('products', function (Blueprint $table) {
                 $table->id();
    $table->unsignedBigInteger('country_id');

    $table->foreignId('category_id')
          ->constrained('catagories')  
          ->cascadeOnUpdate()
          ->restrictOnDelete();
$table->foreignId('subCatagorie_id')
      ->constrained('subcategories')  
      ->cascadeOnUpdate()
      ->restrictOnDelete();

    $table->unsignedTinyInteger('type')
          ->default(1)
          ->comment('1=Warranty,2=Validity');
          $table->string('unit');
    $table->string('product', 191)->unique();

    $table->timestamps();
          });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('products');
        }
    };
