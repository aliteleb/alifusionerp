<?php

namespace Modules\Survey\Filament\Resources\SurveyResource\RelationManagers;

use App\Enums\SurveyQuestionType;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Grid::make()
                    ->schema([
                        Translate::make()
                            ->schema([
                                Forms\Components\TextInput::make('question_text')
                                    ->label(__('Question Text'))
                                    ->required()
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                            ])
                            ->locales(appLocales())
                            ->columnSpanFull(),

                        Forms\Components\ToggleButtons::make('question_type')
                            ->label(__('Question Type'))
                            ->options(SurveyQuestionType::class)
                            ->inline()
                            ->required()
                            ->default(SurveyQuestionType::TEXT)
                            ->columnSpan(2),

                        Forms\Components\Toggle::make('is_required')
                            ->label(__('Required'))
                            ->default(false)
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->columnSpan(1),

                    ])
                    ->columns(4),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->defaultSort('order')
            ->recordTitleAttribute('question_text')
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label(__('Order'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('question_text')
                    ->label(__('Question'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('question_type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (SurveyQuestionType $state) => match ($state) {
                        SurveyQuestionType::TEXT => 'info',
                        SurveyQuestionType::RATING => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (SurveyQuestionType $state) => $state->getLabel())
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_required')
                    ->label(__('Required'))
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('response_count')
                    ->label(__('Responses'))
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('average_score')
                    ->label(__('Avg Score'))
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1) : '-')
                    ->badge()
                    ->color('warning')
                    ->visible(fn ($record) => $record && $record->question_type === SurveyQuestionType::RATING)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('question_type')
                    ->label(__('Question Type'))
                    ->options(SurveyQuestionType::getFilamentOptions())
                    ->placeholder(__('All Types')),

                Tables\Filters\SelectFilter::make('is_required')
                    ->label(__('Required Status'))
                    ->options([
                        1 => __('Required'),
                        0 => __('Optional'),
                    ])
                    ->placeholder(__('All Questions')),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('Add Question'))
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['created_by'] = Auth::id();
                        $data['order'] = $this->getOwnerRecord()->questions()->max('order') + 1;
                        $data['is_active'] = true;

                        return $data;
                    }),
            ])
            ->actions([

                Tables\Actions\EditAction::make()
                    ->color('warning')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['updated_by'] = Auth::id();

                        return $data;
                    }),
                Tables\Actions\ReplicateAction::make()
                    ->label(__('Duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['order'] = $this->getOwnerRecord()->questions()->max('order') + 1;
                        $data['created_by'] = Auth::id();
                        $data['response_count'] = 0;
                        $data['average_score'] = null;
                        $data['skip_count'] = 0;

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\RestoreAction::make()
                    ->color('success'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label(__('Activate Selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(fn ($record) => $record->update(['is_active' => true, 'updated_by' => Auth::id()]));
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label(__('Deactivate Selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('danger')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(fn ($record) => $record->update(['is_active' => false, 'updated_by' => Auth::id()]));
                        })
                        ->requiresConfirmation(),
                ])
                    ->label(__('Bulk Actions'))
                    ->color('primary'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('Create First Question'))
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->poll('30s');
    }
}
