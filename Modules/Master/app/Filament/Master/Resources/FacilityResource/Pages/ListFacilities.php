<?php

namespace Modules\Master\Filament\Master\Resources\FacilityResource\Pages;

use Modules\System\Actions\Facility\SeedFacilityDataAction;
use Modules\Master\Filament\Master\Resources\FacilityResource;
use Modules\Master\Entities\Facility;
use Modules\Core\Observers\FacilityObserver;
use Exception;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ListFacilities extends ListRecords
{
    protected static string $resource = FacilityResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('All'))
                ->badge($this->getModel()::count()),
            'trashed' => Tab::make(__('Trashed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge($this->getModel()::onlyTrashed()->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('Create Facility'))
                ->using(function (array $data): Model {
                    // Temporarily disable the FacilityObserver to prevent transaction conflicts
                    Facility::unsetEventDispatcher();

                    try {
                        // Get the model class from the resource
                        $modelClass = static::getResource()::getModel();

                        // Create the facility record first (without observer)
                        $record = $modelClass::create($data);
                        info($record);
                        // Create tenant database and seed data outside of any transaction
                        // This is done after the facility is created to avoid transaction conflicts
                        $seedAction = new SeedFacilityDataAction;
                        $seedAction->execute($record);

                        return $record;
                    } catch (Exception $e) {
                        // If seeding fails, delete the facility record
                        if (isset($record)) {
                            $record->forceDelete();
                        }
                        throw $e;
                    } finally {
                        // Re-enable the FacilityObserver
                        Facility::observe(FacilityObserver::class);
                        // $facility = Facility::firstWhere('subdomain', $data['subdomain']);
                        // (new \App\Core\Actions\Facility\SeedFacilityDataAction)->execute($facility);
                    }
                }),
        ];
    }
}
