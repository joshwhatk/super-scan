{{--

  $account,
  $scan,
  $messages,
  $added,
  $altered,
  $deleted,
  $altered_files_text,
  $added_files_text,
  $deleted_files_text,

--}}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
  @include('super-scan::emails.head');
  <body>
    <!-- <style> -->
    <table class="body">
      <tr>
        <td class="center" align="center" valign="top">
          <center data-parsed="">
            <table class="container text-center"><tbody><tr><td>
              <table class="row"><tbody><tr>
                    <th class="small-12 large-12 columns first last">
      <table>
        <tr>
          <th>
                  <h2>SuperScan Report</h2>
                  <p class="lead">{{ $account->getName() }}</p>
                  <p class="subheader">
                    Time of Scan: {{ $scan->timestamps['completed']->toDayDateTimeString() }}
                  </p>
                  <p class="subheader">
                    Taking: {{ $scan->timestamps['duration'] }}
                  </p>
                  <hr>
                  @if(! $messages->isEmpty())
                    @foreach($messages as $message)
                    <p class="callout {{ $message['type'] }}">{{ $message['content'] }}</p>
                    @endforeach
                  @endif
                </th>
              <th class="expander"></th>
            </tr>
          </table>
        </th>
              </tr></tbody></table>
              <table class="row"><tbody><tr>
                    <th class="small-12 large-12 columns first last">
      <table>
        <tr>
          <th>
                  <h3>Overview</h3>
                  <p class="text-alert">
                    {{ $altered_files_text }} Modified
                  </p>
                  <p class="text-warning">
                    {{ $added_files_text }} Added
                  </p>
                  <p class="text-primary">
                    {{ $deleted_files_text }} Removed
                  </p>
                  <hr>
                </th>
<th class="expander"></th>
        </tr>
      </table>
    </th>
  </tr>
</tbody>
</table>

@if(! $altered->isEmpty())
<table class="row"><tbody><tr>
  <th class="small-12 large-12 columns first last">
    <table>
      <tr>
        <th>
          <h3>Modified Files</h3>
          <table class="row">
            <tbody>
              <tr>
                <th class="small-7 large-7 columns first">
                  <table>
                    <tr>
                      <th>
                        Filename
                      </th>
                    </tr>
                  </table>
                </th>
                <th class="small-5 large-5 columns last">
                  <table>
                    <tr>
                      <th>
                        Date Modified
                      </th>
                    </tr>
                  </table>
                </th>
              </tr>
            </tbody>
          </table>

          @foreach($altered as $file_path => $file)
          <table class="row">
            <tbody>
              <tr>
                <th class="small-7 large-7 columns first">
                  <table>
                    <tr>
                      <th>
                        <p>{{ $file_path }}</p>
                      </th>
                    </tr>
                  </table>
                </th>
                <th class="small-5 large-5 columns last">
                  <table>
                    <tr>
                      <th>
                        <p>{{ $file->last_modified->toDayDateTimeString() }}</p>
                      </th>
                    </tr>
                  </table>
                </th>
              </tr>
            </tbody>
          </table>
          @endforeach

        </th>
      </tr>
    </table>
  </th>
</tr></tbody></table>
@endif

@if(! $added->isEmpty())
<table class="row"><tbody><tr>
  <th class="small-12 large-12 columns first last">
    <table>
      <tr>
        <th>
          <h3>Added Files</h3>
          <table class="row"><tbody><tr>
            <th class="small-7 large-7 columns first">
              <table>
                <tr>
                  <th>
                    Filename
                  </th>
                </tr>
              </table>
            </th>
            <th class="small-5 large-5 columns last">
              <table>
                <tr>
                  <th>
                    Date Modified
                  </th>
                </tr>
              </table>
            </th>
          </tr></tbody></table>

          @foreach($added as $file_path => $file)
          <table class="row"><tbody><tr>
            <th class="small-7 large-7 columns first">
              <table>
                <tr>
                  <th>
                    <p>{{ $file_path }}</p>
                  </th>
                </tr>
              </table>
            </th>
            <th class="small-5 large-5 columns last">
              <table>
                <tr>
                  <th>
                    <p>{{ $file->last_modified }}</p>
                  </th>
                </tr>
              </table>
            </th>
          </tr></tbody></table>
          @endforeach

        </th>
      </tr>
    </table>
  </th>
</tr></tbody></table>
@endif

@if(! $deleted->isEmpty())
<table class="row"><tbody><tr>
  <th class="small-12 large-12 columns first last">
    <table>
      <tr>
        <th>
          <h3>Removed Files</h3>
          <table class="row"><tbody><tr>
            <th class="small-7 large-7 columns first">
              <table>
                <tr>
                  <th>
                    Filename
                  </th>
                </tr>
              </table>
            </th>
            <th class="small-5 large-5 columns last">
              <table>
                <tr>
                  <th>
                    Date Modified
                  </th>
                </tr>
              </table>
            </th>
          </tr></tbody></table>

          @foreach($deleted as $file_path => $file)
          <table class="row"><tbody><tr>
            <th class="small-7 large-7 columns first">
              <table>
                <tr>
                  <th>
                    <p>{{ $file_path }}</p>
                  </th>
                </tr>
              </table>
            </th>
            <th class="small-5 large-5 columns last">
              <table>
                <tr>
                  <th>
                    <p>{{ $file->last_modified }}</p>
                  </th>
                </tr>
              </table>
            </th>
          </tr></tbody></table>
          @endforeach

        </th>
      </tr>
    </table>
  </th>
</tr></tbody></table>
@endif

<hr>
<table class="row"><tbody><tr>
  <th class="small-12 large-12 columns first last">
    <table>
      <tr>
        <th>
          <h3>Account</h3>
          <p>
            <strong>Name:</strong> {{ $account->getName() }}
          </p>
          <p>
            <strong>Server Hostname:</strong> {{ $account->getServerName() }}
          </p>
          <p>
            <strong>IP Address:</strong> {{ $account->getIpAddress() }}
          </p>
          <p>
            <strong>Directory Scanned:</strong> {{ $account->getScanDirectory() }}
          </p>
          <p>
            <strong>Web URL:</strong> {{ $account->getUrl() }}
          </p>
        </th>
        <th class="expander"></th>
      </tr>
    </table>
  </th>
</tr></tbody></table>
<hr>

<table class="row"><tbody><tr>
  <th class="small-12 large-12 columns first last">
    <table>
      <tr>
        <th>
          <p>Brought to you by <a href="https://github.com/joshwhatk/super-scan">SuperScan</a>.</p>
        </th>
        <th class="expander"></th>
      </tr>
    </table>
  </th>
</tr></tbody></table>
</td></tr></tbody></table>

          </center>
        </td>
      </tr>
    </table>
  </body>
</html>
