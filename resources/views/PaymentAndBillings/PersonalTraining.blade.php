<!-- ====== PERSONAL TRAINING PAGE ====== -->
<div class="page-panel" id="ptPage">
  <div class="card">
    <div class="card-body">
      <div class="section-header">
        <h2 class="card-title">Personal Training Rates</h2>
      </div>

      @if($ptPlans->count() > 0)
      <div class="plan-type-selector">
        @foreach($ptPlans as $plan)
        <div class="plan-type-card{{ $loop->first ? ' active' : '' }}"
             data-plan="{{ $plan->plan_key }}"
             data-price="{{ $plan->price }}"
             data-duration="{{ $plan->duration_days }}">
            <div class="plan-name">{{ $plan->plan_name }}</div>
            <div class="plan-duration">{{ $plan->duration_label ?? ($plan->duration_days . ' ' . ($plan->duration_days === 1 ? 'Day' : 'Days')) }}</div>
            <div class="plan-price">₱{{ number_format($plan->price, 2) }}</div>
            @if($plan->badge_text)
              <div class="plan-badge">{{ $plan->badge_text }}</div>
            @endif
            @if($plan->description)
              <div class="plan-description-text" style="font-size: 0.8125rem; color: #999; margin-top: 0.25rem;">{{ $plan->description }}</div>
            @endif
        </div>
        @endforeach
      </div>
      @else
      <div style="text-align: center; padding: 3rem 2rem;">
        <i class="mdi mdi-dumbbell" style="font-size: 4rem; color: #555; margin-bottom: 1rem; display: block;"></i>
        <p style="color: #999; font-size: 1rem;">No personal training plans configured yet.</p>
        <p style="color: #666; font-size: 0.875rem;">Go to <a href="{{ route('configuration.index') }}" style="color: #FFA726;">Configuration</a> to add PT plans.</p>
      </div>
      @endif

      <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(255, 193, 7, 0.08); border: 1px solid rgba(255, 193, 7, 0.2); border-radius: 8px;">
        <p style="color: #ffc107; margin: 0; font-size: 0.875rem;"><i class="mdi mdi-information"></i> You can manage PT schedules in the <strong>Sessions</strong> module. Rates are managed in <a href="{{ route('configuration.index') }}" style="color: #FFA726;">Configuration</a>.</p>
      </div>
    </div>
  </div>
</div><!-- /ptPage -->
