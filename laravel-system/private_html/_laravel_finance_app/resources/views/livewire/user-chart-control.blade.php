<div class="d-flex justify-content-center bg-dark">
    <div><a class="btn btn-dark" wire:click="decrement" type="button" value="<"><i class="bi bi-caret-left"></i></a></div>

    <div class="btn btn-dark font-weight-bold">{{$speed}} / {{$speedMax}}</div>

    <div><a class="btn btn-dark" wire:click="increment" type="button" value=">"><i class="bi bi-caret-right"></i></a></div>
</div>
