<!-- ====== PERSONAL TRAINING PAGE ====== -->
<div class="page-panel" id="ptPage">
  @if($ptPlans->count() > 0)
  @include('PaymentAndBillings.partials._pt-form')
  @else
  <div class="card">
    <div class="card-body">
      <div style="text-align: center; padding: 3rem 2rem;">
        <i class="mdi mdi-dumbbell" style="font-size: 4rem; color: #555; margin-bottom: 1rem; display: block;"></i>
        <p style="color: #999; font-size: 1rem;">No personal training plans configured yet.</p>
        <p style="color: #666; font-size: 0.875rem;">Go to <a href="{{ route('configuration.index') }}" style="color: #FFA726;">Configuration</a> to add PT plans.</p>
      </div>
    </div>
  </div>
  @endif
</div><!-- /ptPage -->

<!-- PT Modals -->
@include('PaymentAndBillings.partials.modals._pt-modals')
