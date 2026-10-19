<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Filament\Resources\Posts\PostResource;
use App\Filament\Support\CommonActions;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->weight(FontWeight::Medium)
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->state(fn (Post $record): string => PostResource::status($record))
                    ->color(fn (string $state): string => match ($state) {
                        'Publié' => 'success',
                        'Programmé' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('published_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('author')
                    ->label('Auteur')
                    ->toggleable()
                    ->placeholder('—'),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Publiée'),
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(fn (): array => Post::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category', 'category')->all()),
            ])
            ->defaultSort('published_at', 'desc')
            ->emptyStateHeading('Aucune actualité')
            ->recordActions([
                Action::make('view_site')
                    ->label('Voir sur le site')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Post $record): string => route('posts.show', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Post $record): bool => PostResource::status($record) === 'Publié'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...CommonActions::toggleBulkActions('is_published', 'Publier', 'Dépublier'),
                    DeleteBulkAction::make()->modalHeading('Supprimer les actualités sélectionnées'),
                ]),
            ]);
    }
}
