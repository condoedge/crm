<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use App\Kompo\Inscriptions\InscriptionTypeEnum;
use App\Models\Crm\Person;
use App\Models\Inscriptions\InscriptionResidence;
use App\Models\Inscriptions\SpokenLanguageEnum;
use App\Models\Teams\Team;
use Condoedge\Crm\Facades\InscriptionModel;
use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Models\GenderEnum;
use Condoedge\Crm\Models\InscriptionStatusEnum;
use Condoedge\Crm\Models\LinkType;
use Condoedge\Crm\Models\PersonLink;
use Condoedge\Utils\Kompo\Common\Form;
use Condoedge\Utils\Models\ContactInfo\Maps\Address;
use Kompo\Auth\Models\Teams\TeamChange;

class RegisterScoutDrawer extends Form
{
    public $_Title = 'inscriptions.register-a-scout';
    public $noHeaderButtons = true;

    public $class = 'overflow-y-auto mini-scroll w-screen sm:w-[50vw]';
    public $style = 'max-height: 95vh';

    protected $teamId;
    protected $team;

    public function created()
    {
        $this->teamId = $this->prop('team_id') ?? currentTeamId();
        $this->team = Team::findOrFail($this->teamId);
    }

    public function render()
    {
        return _Rows(
            $this->parentSection(),
            $this->scoutSection(),
            $this->relationshipSection(),
            $this->unitSection(),
            $this->familyConditionsSection(),
            $this->supportSection(),
            $this->consentsSection(),
            _FlexEnd(
                _SubmitButton('inscriptions.register')
                    ->closeDrawer()
                    ->alert('alert.scout-registration-created'),
            )->class('mt-2'),
        )->class('p-2 md:p-6 gap-6');
    }

    // ── SECTIONS ──────────────────────────────────────────────

    protected function parentSection()
    {
        return _Rows(
            _MiniTitle('inscriptions.parent-info')->class('mb-2'),
            _CardLevel4(
                _Select()->name('parent_id', false)
                    ->placeholder('inscriptions.search-parent')
                    ->searchOptions(3, 'searchParents')
                    ->class('mb-0')
                    ->onChange(fn($e) => $e->selfGet('loadParentFields')->withAllFormValues()->inPanel('parent-fields-panel')),
            )->p4(),
            _Html('inscriptions.or-add-new-parent')->class('text-center text-sm text-gray-500'),
            _Panel(
                $this->parentFieldsCard(),
            )->id('parent-fields-panel'),
        );
    }

    protected function parentFieldsCard($parent = null)
    {
        return _CardLevel4(
            _Columns(
                _Input('crm.first-name')->name('parent_first_name', false)
                    ->default($parent?->first_name)
                    ->class('mb-0'),
                _Input('crm.last-name')->name('parent_last_name', false)
                    ->default($parent?->last_name)
                    ->class('mb-0'),
            ),
            _Columns(
                _Input('crm.email')->name('parent_email', false)
                    ->default($parent?->email_identity)
                    ->class('mb-0'),
                _Input('crm.phone')->name('parent_phone', false)
                    ->default($parent?->getFirstValidPhone()?->number_ph)
                    ->class('mb-0'),
            )->class('mt-3'),
            _Date('crm.date-of-birth')->name('parent_birth_date', false)
                ->default($parent?->date_of_birth)
                ->class('mb-0 mt-3'),
            _CanadianPlace('helpers.address', 'parent_address')
                ->default(loadFormattedLabel($parent?->getFirstValidAddress()))
                ->class('mb-0 mt-3'),
            SpokenLanguageEnum::getMultiSelect()
                ->name('parent_spoken_languages', false)
                ->default($parent?->spoken_languages ?? array_keys(config('kompo.locales')))
                ->class('mb-0 mt-3'),
        )->p4();
    }

    public function loadParentFields()
    {
        $parent = request('parent_id') ? Person::find(request('parent_id')) : null;

        return $this->parentFieldsCard($parent);
    }

    protected function scoutSection()
    {
        return _Rows(
            _MiniTitle('inscriptions.scout-info')->class('mb-2'),
            _CardLevel4(
                _Select()->name('scout_id', false)
                    ->placeholder('inscriptions.search-scout')
                    ->searchOptions(3, 'searchScouts')
                    ->class('mb-0')
                    ->onChange(fn($e) => $e->selfGet('loadScoutFields')->withAllFormValues()->inPanel('scout-fields-panel')),
            )->p4(),
            _Html('inscriptions.or-add-new-scout')->class('text-center text-sm text-gray-500'),
            _Panel(
                $this->scoutFieldsCard(),
            )->id('scout-fields-panel'),
        );
    }

    protected function scoutFieldsCard($scout = null)
    {
        return _CardLevel4(
            _Columns(
                _Input('crm.first-name')->name('scout_first_name', false)
                    ->default($scout?->first_name)
                    ->class('mb-0'),
                _Input('crm.last-name')->name('scout_last_name', false)
                    ->default($scout?->last_name)
                    ->class('mb-0'),
            ),
            _Columns(
                _Date('crm.date-of-birth')->name('scout_birth_date', false)
                    ->default($scout?->date_of_birth)
                    ->class('mb-0'),
                _Select('inscriptions.gender')->name('scout_gender', false)
                    ->options(GenderEnum::optionsWithLabels())
                    ->default($scout?->gender)
                    ->class('mb-0'),
            )->class('mt-3'),
            _CanadianPlace('helpers.address', 'scout_address')
                ->default(loadFormattedLabel($scout?->getFirstValidAddress()))
                ->class('mb-0 mt-3'),
            SpokenLanguageEnum::getMultiSelect()
                ->name('scout_spoken_languages', false)
                ->default($scout?->spoken_languages ?? array_keys(config('kompo.locales')))
                ->class('mb-0 mt-3'),
        )->p4();
    }

    public function loadScoutFields()
    {
        $scout = request('scout_id') ? Person::find(request('scout_id')) : null;

        return $this->scoutFieldsCard($scout);
    }

    protected function relationshipSection()
    {
        return _Rows(
            _MiniTitle('inscriptions.relationship')->class('mb-2'),
            _CardLevel4(
                _Columns(
                    _Select('inscriptions.link-type')->name('link_type_id', false)
                        ->options(LinkType::pluck('link_name', 'id'))
                        ->default(3)
                        ->class('mb-0'),
                    _Select('inscriptions.lives-with')->name('inscription_residence_id', false)
                        ->options(InscriptionResidence::pluck('currently_residing', 'id'))
                        ->default(3)
                        ->class('mb-0'),
                ),
            )->p4(),
        );
    }

    protected function unitSection()
    {
        if ($this->team->isUnitLevel()) {
            return _Hidden()->name('unit_id')->value($this->teamId);
        }

        if (!$this->team->isGroupLevel()) {
            return null;
        }

        $units = $this->team->teams()->active()->isNotCommittee()->pluck('team_name', 'id');

        return _Rows(
            _MiniTitle('inscriptions.unit')->class('mb-2'),
            _CardLevel4(
                _Select('inscriptions.select-unit')->name('unit_id', false)
                    ->options($units)
                    ->class('mb-0'),
            )->p4(),
        );
    }

    protected function familyConditionsSection()
    {
        return _Rows(
            _MiniTitle('inscriptions.family-conditions-title')->class('mb-2'),
            _CardLevel4(
                _ButtonGroupYesNo2('inscriptions.family-conditions-particularities')
                    ->name('family_conditions', false)
                    ->selfGet('getFamilyConditionsText')->inPanel1()
                    ->class('mb-0'),
                _Panel1(),
            )->p4(),
        );
    }

    public function getFamilyConditionsText($value = null)
    {
        $value = $value ?? request('family_conditions');

        if ($value == BUTTONGROUP_YES) {
            return _Textarea('inscriptions.specify-please')->name('family_conditions_text', false)->class('mt-3');
        }
    }

    protected function supportSection()
    {
        return _Rows(
            _MiniTitle('inscriptions.support-program-title')->class('mb-2'),
            _CardLevel4(
                _ButtonGroupYesNo2('inscriptions.support-program')
                    ->name('in_support_group', false)
                    ->class('mb-0'),
            )->p4(),
        );
    }

    protected function consentsSection()
    {
        return _Rows(
            _MiniTitle('inscriptions.consents')->class('mb-2'),
            _CardLevel4(
                _ButtonGroupYesNo2('inscriptions.consent-to-pictures')
                    ->name('accept_photos', false)
                    ->class('mb-3'),
                _Checkbox('inscriptions.consent-to-care-checkbox')
                    ->name('accept_care', false)
                    ->class('mb-2'),
                _Checkbox('inscriptions.consent-to-privacy-checkbox')
                    ->name('accept_privacy', false)
                    ->class('mb-2'),
                _Checkbox('inscriptions.consent-to-participation-checkbox')
                    ->name('accept_participation', false)
                    ->class('mb-2'),
                _Checkbox('inscriptions.accept-communications')
                    ->name('accept_communications', false)
                    ->class('mb-0'),
            )->p4(),
        );
    }

    // ── HANDLE ────────────────────────────────────────────────

    public function handle()
    {
        $this->validateForm();

        $parent = $this->getOrCreateParent();
        $scout = $this->getOrCreateScout($parent);

        $this->ensurePersonLink($parent, $scout);
        $this->saveScoutExtraFields($scout);

        $targetTeamId = $this->resolveTargetTeamId();
        $targetTeam = Team::findOrFail($targetTeamId);

        $this->validateScoutAge($scout, $targetTeam);

        $yrEvent = $targetTeam->getCurrentYearlyRegistrationEvent();

        if (!$yrEvent) {
            abort(403, __('inscriptions.no-yearly-registration-for-unit', ['unit' => $targetTeam->team_name]));
        }

        $parentInscription = $this->createParentInscription($parent, $targetTeamId, $yrEvent);
        $this->createScoutInscription($scout, $parentInscription, $targetTeamId, $yrEvent);

        TeamChange::addWithMessage(__('inscriptions.with-value-scout-registered-by-admin', [
            'scout' => $scout->full_name,
            'parent' => $parent->full_name,
        ]));
    }

    protected function getOrCreateParent()
    {
        if ($parentId = request('parent_id')) {
            return PersonModel::findOrFail($parentId);
        }

        $email = request('parent_email');

        if (!$email) {
            abort(403, __('inscriptions.parent-email-required'));
        }

        $parent = PersonModel::getOrCreatePersonFromEmail($email);

        if (request('parent_first_name')) {
            $parent->first_name = $parent->first_name ?: request('parent_first_name');
        }
        if (request('parent_last_name')) {
            $parent->last_name = $parent->last_name ?: request('parent_last_name');
        }
        if (request('parent_birth_date')) {
            $parent->date_of_birth = $parent->date_of_birth ?: request('parent_birth_date');
        }
        if ($langs = request('parent_spoken_languages')) {
            $parent->spoken_languages = is_array($langs) ? $langs : [$langs];
        }

        $parent->save();

        if (request('parent_phone')) {
            $parent->createOrDeleteMainPhoneFromNumber(request('parent_phone'));
        }

        if ($addressInput = request('parent_address')) {
            Address::createMainForFromRequest($parent, is_array($addressInput) ? ($addressInput[0] ?? []) : $addressInput);
        }

        return $parent;
    }

    protected function getOrCreateScout($parent)
    {
        if ($scoutId = request('scout_id')) {
            return PersonModel::findOrFail($scoutId);
        }

        $scout = new Person();
        $scout->first_name = request('scout_first_name');
        $scout->last_name = request('scout_last_name');
        $scout->date_of_birth = request('scout_birth_date');
        $scout->gender = request('scout_gender');
        $scout->registered_by = $parent->id;

        if ($langs = request('scout_spoken_languages')) {
            $scout->spoken_languages = is_array($langs) ? $langs : [$langs];
        }

        $scout->save();

        if ($addressInput = request('scout_address')) {
            Address::createMainForFromRequest($scout, is_array($addressInput) ? ($addressInput[0] ?? []) : $addressInput);
        }

        return $scout;
    }

    protected function saveScoutExtraFields($scout)
    {
        $scout->inscription_residence_id = request('inscription_residence_id');
        $scout->family_conditions = request('family_conditions') == BUTTONGROUP_YES ? 1 : 0;
        $scout->family_conditions_text = request('family_conditions_text');
        $scout->in_support_group = request('in_support_group') == BUTTONGROUP_YES ? 1 : 0;
        $scout->accept_photos = request('accept_photos') == BUTTONGROUP_YES ? 1 : 0;
        $scout->accept_care = request('accept_care') ? 1 : 0;
        $scout->accept_privacy = request('accept_privacy') ? 1 : 0;
        $scout->accept_participation = request('accept_participation') ? 1 : 0;
        $scout->accept_communications = request('accept_communications') ? 1 : 0;
        $scout->save();
    }

    protected function ensurePersonLink($parent, $scout)
    {
        $linkTypeId = request('link_type_id', 3);

        PersonLink::upsertLinkBetween($parent, $scout, $linkTypeId);
    }

    protected function resolveTargetTeamId()
    {
        if ($this->team->isUnitLevel()) {
            return $this->teamId;
        }

        $unitId = request('unit_id');

        if (!$unitId) {
            abort(403, __('inscriptions.unit-required'));
        }

        return $unitId;
    }

    protected function validateScoutAge($scout, $targetTeam)
    {
        $branch = $targetTeam->branch;

        if (!$branch || (!$branch->from_age && !$branch->to_age)) {
            return;
        }

        if (!$scout->date_of_birth) {
            return;
        }

        $age = $scout->date_of_birth->age;

        if ($branch->from_age && $age < $branch->from_age) {
            abort(403, __('inscriptions.scout-too-young', [
                'age' => $age,
                'min' => $branch->from_age,
                'unit' => $targetTeam->team_name,
            ]));
        }

        if ($branch->to_age && $age > $branch->to_age) {
            abort(403, __('inscriptions.scout-too-old', [
                'age' => $age,
                'max' => $branch->to_age,
                'unit' => $targetTeam->team_name,
            ]));
        }
    }

    protected function createParentInscription($parent, $targetTeamId, $yrEvent = null)
    {
        $inscription = InscriptionModel::createForMainPerson(
            $parent->id,
            $targetTeamId,
            InscriptionTypeEnum::BY_PARENT,
        );

        $inscription->event_id = $yrEvent?->id;
        $inscription->status = InscriptionStatusEnum::FILLED;
        $inscription->save();

        return $inscription;
    }

    protected function createScoutInscription($scout, $parentInscription, $targetTeamId, $yrEvent = null)
    {
        $inscription = new (InscriptionModel::getClass());
        $inscription->person_id = $scout->id;
        $inscription->team_id = $targetTeamId;
        $inscription->event_id = $yrEvent?->id;
        $inscription->type = InscriptionTypeEnum::BY_PARENT->value;
        $inscription->inscribed_by = $parentInscription->inscribed_by;
        $inscription->invited_by = auth()->id();
        $inscription->related_inscription_id = $parentInscription->id;
        $inscription->status = InscriptionStatusEnum::FILLED;
        $inscription->save();

        return $inscription;
    }

    // ── SEARCH ────────────────────────────────────────────────

    public function searchParents($search)
    {
        return Person::active()
            ->with(['personTeams' => fn ($q) => $q->roleHierarchy()])
            ->search($search)->take(20)->get()->mapWithKeys(fn ($p) => [
                $p->id => _Rows(
                    _Html($p->full_name),
                    _Rows($p->personTeams->map(
                        fn ($pr) =>
                        _Html($pr->team->team_name . ' - ' . $pr->getRoleName())->class('text-xs text-gray-600')
                    )),
                ),
            ]);
    }

    public function searchScouts($search)
    {
        return Person::active()
            ->with(['personTeams' => fn ($q) => $q->roleHierarchy()])
            ->search($search)->take(20)->get()->mapWithKeys(fn ($p) => [
                $p->id => _Rows(
                    _Html($p->full_name),
                    _Html($p->age_label)->class('text-xs text-gray-600'),
                ),
            ]);
    }

    // ── VALIDATION ──────────────────────────────────────────────

    protected function validateForm()
    {
        if (!request('parent_id') && !request('parent_email')) {
            abort(403, __('inscriptions.parent-email-required'));
        }

        if (!request('parent_id') && request('parent_email') && !filter_var(request('parent_email'), FILTER_VALIDATE_EMAIL)) {
            abort(403, __('validation.email', ['attribute' => __('crm.email')]));
        }

        if (!request('scout_id') && (!request('scout_first_name') || !request('scout_last_name'))) {
            abort(403, __('inscriptions.scout-name-required'));
        }
    }
}
