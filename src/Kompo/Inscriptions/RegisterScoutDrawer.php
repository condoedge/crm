<?php

namespace Condoedge\Crm\Kompo\Inscriptions;

use App\Kompo\Traits\PersonSearchTrait;
use App\Models\Teams\Team;
use App\Services\Inscriptions\Results\RegisterScoutDTO;
use App\Services\Inscriptions\UseCases\RegisterPersonByAdminUseCase;
use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Utils\Kompo\Common\Form;
use Condoedge\Utils\Models\ContactInfo\Maps\Address;
use Condoedge\Utils\Rule\MaxAgeRule;
use Condoedge\Utils\Rule\MinAgeRule;
use Kompo\Auth\Models\Teams\TeamChange;

class RegisterScoutDrawer extends Form
{
    use PersonSearchTrait;

    public $_Title = 'inscriptions.register-a-scout';
    public $noHeaderButtons = true;

    public $class = 'overflow-y-auto mini-scroll w-screen sm:w-[50vw]';
    public $style = 'max-height: 95vh';

    public $permissionKey = 'register_a_scout';

    protected $team;

    public function created()
    {
        $this->team = Team::findOrFail($this->prop('team_id') ?? currentTeamId());
    }

    // ── HANDLE ──────────────────────────────────────────────────

    public function handle()
    {
        $targetTeamId = $this->resolveTargetTeamId();
        $targetTeam = Team::findOrFail($targetTeamId);

        $yrEvent = $targetTeam->getCurrentYearlyRegistrationEvent();

        if (!$yrEvent) {
            throwValidationError('unit_id', __('inscriptions.no-yearly-registration-for-unit', ['unit' => $targetTeam->team_name]));
        }

        $existingParentId = request('parent_id') ? (int) request('parent_id') : null;
        $existingChildId = request('scout_id') ? (int) request('scout_id') : null;

        $result = app(RegisterPersonByAdminUseCase::class)->registerScout(new RegisterScoutDTO(
            parentData: [
                'first_name' => request('parent_first_name'),
                'last_name' => request('parent_last_name'),
                'email' => request('parent_email'),
                'phone' => request('parent_phone'),
            ],
            childData: [
                'first_name' => request('scout_first_name'),
                'last_name' => request('scout_last_name'),
            ],
            teamId: $targetTeamId,
            linkTypeId: (int) request('link_type_id', 3),
            existingParentId: $existingParentId,
            existingChildId: $existingChildId,
        ));

        // Link inscriptions to the yearly registration event
        $result->parentInscription->update(['event_id' => $yrEvent->id]);
        $result->childInscription->update(['event_id' => $yrEvent->id]);

        $parent = PersonModel::findOrFail($result->parentInscription->inscribed_by);
        $scout = PersonModel::findOrFail($result->childInscription->person_id);

        if (!$existingParentId) {
            $this->applyExtraPersonFields($parent, 'parent');
        }

        if (!$existingChildId) {
            $this->applyExtraScoutFields($scout, $parent);
        }

        $this->applyScoutInscriptionFields($scout);

        TeamChange::addWithMessage(__('inscriptions.with-value-scout-registered-by-admin', [
            'scout' => $scout->full_name,
            'parent' => $parent->full_name,
        ]));

        return response()->kompoMulti([
            response()->closeDrawer(),
            response()->kompoAlert(__('alert.scout-registration-created', [
                'scout' => $scout->full_name,
            ])),
        ]);
    }

    // ── LAYOUT ──────────────────────────────────────────────────

    public function render()
    {
        return _Rows(
            $this->parentSection(),
            $this->scoutSection(),
            $this->relationshipSection(),
            $this->unitSection(),
            $this->familySection(),
            $this->consentsSection(),
            _FlexEnd(
                _SubmitButton('inscriptions.register'),
            )->class('mt-2'),
        )->class('p-2 md:p-6 gap-6');
    }

    // ── SECTIONS ────────────────────────────────────────────────

    protected function parentSection()
    {
        return _Rows(
            _MiniTitle('inscriptions.parent-info')->class('mb-2'),
            _CardLevel4(
                _Select()->name('parent_id', false)
                    ->placeholder('inscriptions.search-parent')
                    ->searchOptions(3, 'searchPersons')
                    ->run('({ value, el }) => {
                        if (value) {
                            el("parent-fields-card").hide();
                            el("parent-or-label").hide();
                        } else {
                            el("parent-fields-card").show();
                            el("parent-or-label").show();
                        }
                    }'),
            )->p4(),
            _Html('inscriptions.or-add-new-parent')->id('parent-or-label')
                ->class('text-center text-sm text-gray-500'),
            _CardLevel4(
                _Rows(array_merge(
                    _PersonInfoFields('parent'),
                    _PersonExtendedFields('parent'),
                )),
            )->id('parent-fields-card')->p4(),
        );
    }

    protected function scoutSection()
    {
        return _Rows(
            _MiniTitle('inscriptions.scout-info')->class('mb-2'),
            _CardLevel4(
                _Select()->name('scout_id', false)
                    ->placeholder('inscriptions.search-scout')
                    ->searchOptions(3, 'searchPersonsSimple')
                    ->run('({ value, el }) => {
                        if (value) {
                            el("scout-fields-card").hide();
                            el("scout-or-label").hide();
                        } else {
                            el("scout-fields-card").show();
                            el("scout-or-label").show();
                        }
                    }'),
            )->p4(),
            _Html('inscriptions.or-add-new-scout')->id('scout-or-label')
                ->class('text-center text-sm text-gray-500'),
            _CardLevel4(
                _Rows(_ScoutInfoFields()),
            )->id('scout-fields-card')->p4(),
        );
    }

    protected function relationshipSection()
    {
        return _Rows(
            _MiniTitle('inscriptions.relationship')->class('mb-2'),
            _CardLevel4(
                _Columns(
                    _LinkTypeSelect(),
                    _ResidenceSelect(),
                ),
            )->p4(),
        );
    }

    protected function unitSection()
    {
        if ($this->team->isUnitLevel()) {
            return _Hidden()->name('unit_id')->value($this->team->id);
        }

        if (!$this->team->isGroupLevel()) {
            return null;
        }

        $units = $this->team->teams()->active()->isNotCommittee()
            ->with('branch')->get()
            ->mapWithKeys(fn ($unit) => [
                $unit->id => $unit->team_name . ($unit->branch ? ' (' . $unit->branch->showAges() . ')' : ''),
            ]);

        return _Rows(
            _MiniTitle('inscriptions.unit')->class('mb-2'),
            _CardLevel4(
                _Select('inscriptions.select-unit')->name('unit_id', false)
                    ->options($units),
            )->p4(),
        );
    }

    protected function familySection()
    {
        return _Rows(
            _MiniTitle('inscriptions.family-conditions-title')->class('mb-2'),
            _CardLevel4(
                _Rows(array_merge(
                    _FamilyConditionsFields(),
                    [_SupportProgramField()->class('mt-4')],
                )),
            )->p4(),
        );
    }

    protected function consentsSection()
    {
        return _Rows(
            _MiniTitle('inscriptions.consents')->class('mb-2'),
            _CardLevel4(
                _Rows(_ScoutConsentFields()),
            )->p4(),
        );
    }

    // ── VALIDATION ──────────────────────────────────────────────

    public function rules()
    {
        $rules = [
            'link_type_id' => 'required',
        ];

        if (!request('parent_id')) {
            $rules['parent_first_name'] = 'required';
            $rules['parent_last_name'] = 'required';
            $rules['parent_email'] = 'required|email';
        }

        if (!request('scout_id')) {
            $rules['scout_first_name'] = 'required';
            $rules['scout_last_name'] = 'required';
            $rules['scout_birth_date'] = ['required', 'date'];
        }

        if (!$this->team->isUnitLevel()) {
            $rules['unit_id'] = 'required';
        }

        // Branch age validation using existing rules
        $branch = $this->resolveTargetBranch();

        if ($branch && !request('scout_id')) {
            $dobRules = $rules['scout_birth_date'] ?? ['required', 'date'];

            if ($branch->from_age) {
                $dobRules[] = new MaxAgeRule($branch->to_age); // DOB must not make them older than max
            }
            if ($branch->to_age) {
                $dobRules[] = new MinAgeRule($branch->from_age); // DOB must not make them younger than min
            }

            $rules['scout_birth_date'] = $dobRules;
        }

        return $rules;
    }

    // ── HANDLE HELPERS ──────────────────────────────────────────

    protected function resolveTargetTeamId()
    {
        return $this->team->isUnitLevel()
            ? $this->team->id
            : request('unit_id');
    }

    protected function resolveTargetBranch()
    {
        if ($this->team->isUnitLevel()) {
            return $this->team->branch;
        }

        $unitId = request('unit_id');

        return $unitId ? Team::find($unitId)?->branch : null;
    }

    protected function applyExtraPersonFields($person, $prefix)
    {
        $person->date_of_birth = $person->date_of_birth ?: request("{$prefix}_birth_date");

        if ($langs = request("{$prefix}_spoken_languages")) {
            $person->spoken_languages = is_array($langs) ? $langs : [$langs];
        }

        $person->save();

        if ($address = request("{$prefix}_address")) {
            Address::createMainForFromRequest($person, is_array($address) ? ($address[0] ?? []) : $address);
        }
    }

    protected function applyExtraScoutFields($scout, $parent)
    {
        $scout->date_of_birth = request('scout_birth_date');
        $scout->gender = request('scout_gender');
        $scout->registered_by = $parent->id;

        if ($langs = request('scout_spoken_languages')) {
            $scout->spoken_languages = is_array($langs) ? $langs : [$langs];
        }

        $scout->save();

        if ($address = request('scout_address')) {
            Address::createMainForFromRequest($scout, is_array($address) ? ($address[0] ?? []) : $address);
        }
    }

    protected function applyScoutInscriptionFields($scout)
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
}
