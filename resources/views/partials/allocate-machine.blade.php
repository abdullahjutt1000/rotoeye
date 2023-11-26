<div class="form-group form-material col-md-4">
    <label class="form-control-label" for="section">Select Machine</label>
    <select class="form-control" id="machine" name="machines[]" required>
        @foreach($machines as $machine)
            <option value="{{$machine->id}}">{{$machine->section->department->businessUnit->company->name.' - '.$machine->section->department->businessUnit->business_unit_name.' - '.$machine->name.' - '.$machine->sap_code}}</option>
        @endforeach
    </select>
</div>