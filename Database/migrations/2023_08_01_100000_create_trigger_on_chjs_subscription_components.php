<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        DB::unprepared('
            CREATE TRIGGER tr_OnInsertIsMainComponent
            AFTER INSERT ON chjs_subscription_components
            FOR EACH ROW
            BEGIN
                IF NEW.is_main_component = 1 THEN
                    UPDATE chjs_subscriptions
                    SET component_handle = NEW.component_handle
                    WHERE subscription_id = NEW.subscription_id;
                END IF;
            END;
        ');

        DB::unprepared('
            CREATE TRIGGER tr_OnUpdateIsMainComponent
            AFTER UPDATE ON chjs_subscription_components
            FOR EACH ROW
            BEGIN
                IF NEW.is_main_component = 1 THEN
                    UPDATE chjs_subscriptions
                    SET component_handle = NEW.component_handle
                    WHERE subscription_id = NEW.subscription_id;
                END IF;
            END;
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS tr_OnInsertIsMainComponent');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_OnUpdateIsMainComponent');
    }
};
