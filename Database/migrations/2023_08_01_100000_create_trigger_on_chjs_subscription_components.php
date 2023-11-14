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
                DECLARE fetchedComponentEntry VARCHAR(21);
                DECLARE fetchedComponentHandle VARCHAR(32);
                IF NEW.is_main_component = 1 THEN
                
                    SELECT component_entry, component_handle
                    INTO fetchedComponentEntry, fetchedComponentHandle
                    FROM chjs_components
                    WHERE component_id = NEW.component_id;
        
                    UPDATE chjs_subscriptions
                    SET component = fetchedComponentEntry, component_handle = fetchedComponentHandle
                    WHERE subscription_id = NEW.subscription_id;
                END IF;
            END;
        ');

        DB::unprepared('
            CREATE TRIGGER tr_OnUpdateIsMainComponent
            AFTER UPDATE ON chjs_subscription_components
            FOR EACH ROW
            BEGIN
                DECLARE fetchedComponentEntry VARCHAR(21);
                DECLARE fetchedComponentHandle VARCHAR(32);
                IF NEW.is_main_component = 1 THEN
                
                    SELECT component_entry, component_handle
                    INTO fetchedComponentEntry, fetchedComponentHandle
                    FROM chjs_components
                    WHERE component_id = NEW.component_id;
        
                    UPDATE chjs_subscriptions
                    SET component = fetchedComponentEntry, component_handle = fetchedComponentHandle
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
