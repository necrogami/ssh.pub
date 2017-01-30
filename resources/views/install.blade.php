mkdir -p $HOME/.ssh
chmod 700 $HOME/.ssh
touch $HOME/.ssh/authorized_keys
chmod 600 $HOME/.ssh/authorized_keys
@foreach ($keys as $key)
echo "# {{ $key['keyname'] }}
echo "{{ $key['key'] }}" >> $HOME/.ssh/authorized_keys
@endforeach
