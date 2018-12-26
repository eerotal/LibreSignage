<!-- Slide editable status labels -->
<div id="slide-label-readonly">
	You can't edit this slide.
</div>
<div id="slide-label-edited">
	You can't edit this slide because someone
	else is already editing it.
</div>
<div id="slide-label-collaborate">
	You can edit this slide as a collaborator.
</div>

<!-- Slide name input -->
<div class="form-group" id="slide-name-group">
	<label for="slide-name">Name</label>
	<input type="text"
		class="form-control w-100"
		id="slide-name"
		data-toggle="tooltip"
		title="The name of the slide. This is only visible in the editor.">
	<div class="invalid-feedback"></div>
</div>

<!-- Slide owner label -->
<div class="form-group" id="slide-owner-group">
	<label for="slide-owner">Owner</label>
	<input type="text"
		class="form-control w-100"
		id="slide-owner"
		data-toggle="tooltip"
		title="The owner of the slide."
		disabled>
</div>

<!-- Slide collaborators multiselect -->
<div class="form-group" id="slide-collab-group">
	<label for="slide-collab">
		Collaborators
	</label>
</div>

<!-- Slide duration selector -->
<div class="form-group" id="slide-duration-group">
	<label for="slide-duration">Duration (seconds)</label>
	<input type="number" class="form-control w-100"
		id="slide-duration"
		data-toggle="tooltip"
		title="The duration of the slide in seconds.">
	</input>
	<div class="invalid-feedback"></div>
</div>

<!-- Slide index input -->
<div class="form-group" id="slide-index-group">
	<label for="slide-index">Index</label>
	<input type="number"
		min="0"
		class="form-control w-100"
		id="slide-index"
		data-toggle="tooltip"
		title="The ordinal number of the slide. 0 is the first slide.">
	<div class="invalid-feedback"></div>
</div>

<!-- Slide animation selector -->
<div class="form-group" id="slide-animation-group">
	<label for="slide-animation">Animation</label>
	<select class="custom-select w-100"
		id="slide-animation"
		data-toggle="tooltip"
		title="Slide transition animation.">
		<option value="0">No animation</option>
		<option value="1">Swipe left</option>
		<option value="2">Swipe right</option>
		<option value="3">Swipe up</option>
		<option value="4">Swipe down</option>
	</select>
</div>

<!-- Schedule enable -->
<div class="form-group mb-0">
	<a class="link-nostyle"
		data-toggle="collapse"
		href="#slide-sched-group"
		aria-expanded="false"
		aria-controls="slide-sched-group">
		<i class="fas fa-angle-right"></i> Slide scheduling
	</a>
</div>

<!-- Schedule date/time selector -->
<div class="row form-group collapse" id="slide-sched-group">
	<div class="col-12 py-1">
		<input type="checkbox"
			id="slide-schedule-enable"
			data-toggle="tooltip"
			title="Select whether the slide is scheduled.">
		<label class="form-check-label" for="slide-sched">
			Enable
		</label>
	</div>
	<div class="col-12 py-1">
		<label for="slide-sched-date-s">
			Start date
		</label>
		<input type="date"
			id="slide-sched-date-s"
			class="form-control d-inline"
			data-toggle="tooltip"
			title="The slide schedule start date.">
	</div>
	<div class="col-12 py-1">
		<input type="time"
			id="slide-sched-time-s"
			class="form-control d-inline"
			data-toggle="tooltip"
			title="The slide schedule start time."
			step="1">
	</div>
	<div class="col-12 py-1">
		<label for="slide-sched-date-e">
			End date
		</label>
		<input type="date"
			id="slide-sched-date-e"
			class="form-control d-inline"
			data-toggle="tooltip"
			title="The slide schedule end date.">
	</div>
	<div class="col-12 py-1">
		<input type="time"
			id="slide-sched-time-e"
			class="form-control d-inline"
			data-toggle="tooltip"
			title="The slide schedule end time."
			step="1">
	</div>
</div>

<!-- Slide enabled checkbox -->
<div class="form-group mt-3" id="slide-enabled-group">
	<input type="checkbox"
		id="slide-enable"
		data-toggle="tooltip"
		title="Select whether the slide is enabled or not.">
	<label class="form-check-label"
		for="slide-enabled">
		Enable slide
	</label>
</div>
